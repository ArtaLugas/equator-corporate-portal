<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Database\Eloquent\Builder;

/**
 * Single source of truth for lead (contact-message) analytics. The dashboard
 * reads everything via summary() — no aggregation query is written twice.
 *
 * Spam is excluded; soft-deleted rows are excluded by the model's global scope.
 */
class LeadAnalytics
{
    private function base(): Builder
    {
        return Message::query()->where('status', '!=', Message::STATUS_SPAM);
    }

    /** Everything the dashboard needs, in one call. */
    public function summary(): array
    {
        return [
            'total' => $this->base()->count(),
            'top_landing_pages' => $this->topLandingPages(),
            'top_campaigns' => $this->topCampaigns(),
            'top_referrers' => $this->topReferrers(),
            'top_locales' => $this->topLocales(),
            'leads_per_month' => $this->leadsPerMonth(),
        ];
    }

    /** Landing pages folded to their path (drops query strings). */
    public function topLandingPages(int $limit = 8): array
    {
        return $this->foldBy('landing_page', fn ($url) => parse_url($url, PHP_URL_PATH) ?: $url, $limit);
    }

    /** Referrers folded to their host. */
    public function topReferrers(int $limit = 8): array
    {
        return $this->foldBy('referrer', fn ($ref) => parse_url($ref, PHP_URL_HOST) ?: $ref, $limit);
    }

    public function topCampaigns(int $limit = 8): array
    {
        return $this->groupCount('utm_campaign', $limit);
    }

    public function topLocales(int $limit = 20): array
    {
        return $this->groupCount('locale', $limit);
    }

    /** Leads per month for the last $months, chronological, gaps filled with 0. */
    public function leadsPerMonth(int $months = 12): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $rows = $this->base()
            ->where('created_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as c")
            ->groupBy('ym')
            ->pluck('c', 'ym');

        $out = [];
        for ($i = 0; $i < $months; $i++) {
            $key = $start->copy()->addMonths($i)->format('Y-m');
            $out[$key] = (int) ($rows[$key] ?? 0);
        }

        return $out;
    }

    /** Direct DB group-count on a single column → ['value' => count] desc. */
    private function groupCount(string $column, int $limit): array
    {
        return $this->base()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("`{$column}` as v, COUNT(*) as c")
            ->groupBy($column)
            ->orderByDesc('c')
            ->limit($limit)
            ->pluck('c', 'v')
            ->map(fn ($c) => (int) $c)
            ->all();
    }

    /** Group-count then normalise each value in PHP (e.g. URL → path/host). */
    private function foldBy(string $column, callable $normalize, int $limit): array
    {
        $rows = $this->base()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->selectRaw("`{$column}` as v, COUNT(*) as c")
            ->groupBy($column)
            ->get();

        $folded = [];
        foreach ($rows as $row) {
            $key = $normalize($row->v) ?: '—';
            $folded[$key] = ($folded[$key] ?? 0) + (int) $row->c;
        }

        arsort($folded);

        return array_slice($folded, 0, $limit, true);
    }
}

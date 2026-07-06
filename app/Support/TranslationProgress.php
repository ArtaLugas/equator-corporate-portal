<?php

namespace App\Support;

use App\Models\AboutContent;
use App\Models\AboutHistory;
use App\Models\AboutSection;
use App\Models\CompanyCredential;
use App\Models\CompanyCredentialItem;
use App\Models\CompanyDocument;
use App\Models\CoreValue;
use App\Models\Faq;
use App\Models\HeroBanner;
use App\Models\KeyMetric;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\OfficeLocation;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Team;
use Illuminate\Database\Eloquent\Model;

/**
 * Roll-up of Indonesian (non-default locale) translation completeness per content
 * module. Shared by the `i18n:status` console command and the admin Translation
 * Progress page. Read-only — uses the HasTranslations trait's translationProgress()
 * (which measures each record against the fields that have content in the default
 * locale), so it does NOT change any fallback behaviour.
 */
class TranslationProgress
{
    /** Public content modules that carry translatable fields (label → model → admin index route). */
    public const MODULES = [
        ['label' => 'Services', 'model' => Service::class, 'route' => 'admin.services.index'],
        ['label' => 'Service Categories', 'model' => ServiceCategory::class, 'route' => 'admin.service-categories.index'],
        ['label' => 'Projects', 'model' => Project::class, 'route' => 'admin.projects.index'],
        ['label' => 'News', 'model' => News::class, 'route' => 'admin.news.index'],
        ['label' => 'News Categories', 'model' => NewsCategory::class, 'route' => 'admin.news-categories.index'],
        ['label' => 'About — Sections', 'model' => AboutSection::class, 'route' => 'admin.about-sections.index'],
        ['label' => 'About — Contents', 'model' => AboutContent::class, 'route' => 'admin.about-contents.index'],
        ['label' => 'About — Histories', 'model' => AboutHistory::class, 'route' => 'admin.about-histories.index'],
        ['label' => 'FAQ', 'model' => Faq::class, 'route' => 'admin.faqs.index'],
        ['label' => 'Core Values', 'model' => CoreValue::class, 'route' => 'admin.core-values.index'],
        ['label' => 'Teams', 'model' => Team::class, 'route' => 'admin.teams.index'],
        ['label' => 'Hero Banners', 'model' => HeroBanner::class, 'route' => 'admin.hero-banners.index'],
        ['label' => 'Key Metrics', 'model' => KeyMetric::class, 'route' => 'admin.key-metrics.index'],
        ['label' => 'Company Documents', 'model' => CompanyDocument::class, 'route' => 'admin.company-documents.index'],
        ['label' => 'Office Locations', 'model' => OfficeLocation::class, 'route' => 'admin.office-locations.index'],
        ['label' => 'Company Credentials', 'model' => CompanyCredential::class, 'route' => 'admin.company-credentials.index'],
        ['label' => 'Credential Items', 'model' => CompanyCredentialItem::class, 'route' => 'admin.company-credentials.index'],
    ];

    /**
     * Per-module stats for a target locale. Each row:
     *   label, route, total, complete, partial, untranslated, percent
     * `percent` is the average per-record completeness (0–100). Soft-deleted rows
     * are excluded (SoftDeletes default scope).
     */
    public static function forLocale(?string $locale = null): array
    {
        $locale ??= self::firstNonDefaultLocale();

        $rows = [];

        foreach (self::MODULES as $module) {
            /** @var class-string<Model> $model */
            $model = $module['model'];

            $records = $model::query()->get();
            $total = $records->count();

            $complete = $partial = $untranslated = $sum = 0;

            foreach ($records as $record) {
                $p = $record->translationProgress($locale);
                $sum += $p;

                if ($p === 100) {
                    $complete++;
                } elseif ($p === 0) {
                    $untranslated++;
                } else {
                    $partial++;
                }
            }

            $rows[] = [
                'label' => $module['label'],
                'route' => $module['route'],
                'total' => $total,
                'complete' => $complete,
                'partial' => $partial,
                'untranslated' => $untranslated,
                'percent' => $total > 0 ? (int) round($sum / $total) : 100,
            ];
        }

        return $rows;
    }

    /** Overall completeness across all modules (record-weighted average). */
    public static function overallPercent(array $rows): int
    {
        $total = array_sum(array_column($rows, 'total'));

        if ($total === 0) {
            return 100;
        }

        // Re-weight each module's average by its record count.
        $weighted = 0;
        foreach ($rows as $row) {
            $weighted += $row['percent'] * $row['total'];
        }

        return (int) round($weighted / $total);
    }

    /** True when every module is fully translated for the locale. */
    public static function isComplete(array $rows): bool
    {
        foreach ($rows as $row) {
            if ($row['total'] > 0 && $row['complete'] !== $row['total']) {
                return false;
            }
        }

        return true;
    }

    public static function firstNonDefaultLocale(): string
    {
        $default = config('locales.default');

        foreach (array_keys(config('locales.supported', [])) as $locale) {
            if ($locale !== $default) {
                return $locale;
            }
        }

        return $default;
    }
}

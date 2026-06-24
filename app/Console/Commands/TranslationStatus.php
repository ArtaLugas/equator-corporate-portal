<?php

namespace App\Console\Commands;

use App\Support\TranslationProgress;
use Illuminate\Console\Command;

/**
 * Quick per-module translation completeness report, e.g. for checking how close
 * each module is to being publishable in Indonesian.
 *
 *   php artisan i18n:status
 *   php artisan i18n:status --locale=id
 */
class TranslationStatus extends Command
{
    protected $signature = 'i18n:status {--locale= : Target locale (defaults to the first non-default locale)}';

    protected $description = 'Show per-module translation completeness for a locale';

    public function handle(): int
    {
        $locale = $this->option('locale') ?: TranslationProgress::firstNonDefaultLocale();

        if (! array_key_exists($locale, config('locales.supported', []))) {
            $this->error("Unknown locale [{$locale}]. Supported: ".implode(', ', array_keys(config('locales.supported', []))));

            return self::FAILURE;
        }

        $rows = TranslationProgress::forLocale($locale);

        $this->newLine();
        $this->line("  Translation progress — locale <fg=cyan>[{$locale}]</> (vs default source)");
        $this->newLine();

        $this->table(
            ['Module', 'Total', 'Complete', 'Partial', 'Untranslated', '% done'],
            collect($rows)->map(fn ($r) => [
                $r['label'],
                $r['total'],
                $r['complete'],
                $r['partial'] ?: '-',
                $r['untranslated'] ?: '-',
                $this->bar($r['percent']),
            ])->all()
        );

        $overall = TranslationProgress::overallPercent($rows);
        $complete = TranslationProgress::isComplete($rows);

        $this->newLine();
        $this->line("  Overall: {$this->bar($overall)}   ".($complete
            ? '<fg=green;options=bold>ALL MODULES COMPLETE ✓</>'
            : '<fg=yellow>in progress</>'));
        $this->newLine();

        return self::SUCCESS;
    }

    private function bar(int $percent): string
    {
        $color = $percent === 100 ? 'green' : ($percent === 0 ? 'red' : 'yellow');

        return "<fg={$color}>{$percent}%</>";
    }
}

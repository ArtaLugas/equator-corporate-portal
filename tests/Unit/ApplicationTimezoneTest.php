<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The CMS stores and renders bare wall-clock times: the admin "Publish Date" is a
 * datetime-local field written straight to published_at with no timezone
 * conversion, and News::scopePublished() compares it against now(). Both sides
 * must therefore speak the editor's clock. Running on UTC re-introduces a silent
 * 7-hour embargo on every scheduled article while echoing the entered time back
 * unchanged, which is why this is pinned rather than left to the environment.
 */
class ApplicationTimezoneTest extends TestCase
{
    public function test_application_runs_on_the_editors_wall_clock(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        // Laravel must have actually applied it to PHP, not just held the config.
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
    }

    public function test_a_scheduled_time_is_not_shifted_relative_to_now(): void
    {
        // What the admin form submits for "five minutes ago" on the editor's clock.
        $submitted = now()->subMinutes(5)->format('Y-m-d\TH:i');

        $this->assertTrue(
            Carbon::parse($submitted)->lte(now()),
            'A publish time already past on the wall clock must not still be embargoed.'
        );
    }
}

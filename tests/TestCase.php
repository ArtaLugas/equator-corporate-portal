<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Seed only the RBAC roles/permissions, and only ONCE per test run.
     * RefreshDatabase honours these properties when it first migrates — before
     * any per-test transaction — so the 90 permissions are created a single time
     * for the whole suite rather than re-seeded inside every test (which added
     * ~8x wall-clock). Tests that do not use RefreshDatabase ignore these.
     *
     * The seeder only inserts roles/permissions (its admin-assignment loop is a
     * no-op on the empty test database), so it never perturbs row-count
     * assertions in unrelated tests.
     */
    protected bool $seed = true;

    protected string $seeder = RolePermissionSeeder::class;
}

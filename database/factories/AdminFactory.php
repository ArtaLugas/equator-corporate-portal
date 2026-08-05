<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ];
    }

    /**
     * Attach the spatie role matching the `role` column so a factory-built admin
     * carries the same permissions it would in production. No-ops when the roles
     * have not been seeded (e.g. a test that never seeds RBAC), keeping the
     * factory usable without a hard dependency on the seeder.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Admin $admin) {
            $role = $admin->role === 'super_admin' ? 'super_admin' : 'admin';

            if (Role::where('name', $role)->where('guard_name', Rbac::GUARD)->exists()) {
                $admin->assignRole($role);
            }
        });
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => 'super_admin']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}

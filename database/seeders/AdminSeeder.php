<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    use LoadsSeedData;

    public function run(): void
    {
        foreach ($this->loadData('admins') as $row) {

            // Map legacy role "superadmin" → "super_admin".
            $role = ($row['role'] ?? '') === 'superadmin' ? 'super_admin' : 'admin';

            // NOTE: passwords keep their original bcrypt hashes. We use the
            // query builder (not the model) so the "hashed" cast does not
            // re-hash an already-hashed value.
            DB::table('admins')->updateOrInsert(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => $row['password'],
                    'avatar' => $this->nullable($row['profile_picture'] ?? null),
                    'role' => $role,
                    'status' => $row['status'] ?? 'active',
                    'last_login_at' => $this->nullable($row['last_login_at'] ?? null),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }
}

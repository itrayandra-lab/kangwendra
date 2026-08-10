<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * SEEDER INI UNTUK FRESH CLONE / INSTALL SAJA.
     * Jika database SUDAH ADA data, seeder ini TIDAK AKAN jalan
     * (guard: only seed if no users exist).
     *
     * Credentials default setelah fresh clone:
     *   Email:    admin@kangwendra.com
     *   Password: password
     *
     * Ganti credentials setelah login!
     */
    public function run(): void
    {
        // Guard: jangan overwrite jika sudah ada user
        if (User::count() > 0) {
            $this->command->info('Users already exist — skipping AdminUserSeeder.');
            return;
        }

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@kangwendra.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'failed_upload_attempts' => 0,
        ]);

        // Assign role admin
        $admin->assignRole('admin');

        $this->command->info('Admin user created: admin@kangwendra.com / password');
        $this->command->warn('GANTI password setelah login!');
    }
}

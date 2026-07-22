<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        # Daftar semua permissions berdasarkan data di tabel Anda
        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view posts', 'create posts', 'edit posts', 'delete posts',
            'manage roles', 'manage permissions',
            'manage web identities',
            'view pages', 'create pages', 'edit pages', 'delete pages',
            'view menu', 'create menu', 'edit menu', 'delete menu',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            'view album', 'create album', 'edit album', 'delete album',
            'view video', 'create video', 'edit video', 'delete video',
            'view information', 'create information', 'edit information', 'delete information',
        ];

        # Buat permissions kalau belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        # Definisikan roles beserta permissions-nya
        $roles = [
            'admin' => $permissions, 
            'editor' => [
                'view posts', 'create posts', 'edit posts', 'delete posts',
                'view pages', 'create pages', 'edit pages', 'delete pages',
                'view menu', 'create menu', 'edit menu', 'delete menu',
                'view categories', 'create categories', 'edit categories', 'delete categories',
                'view tags', 'create tags', 'edit tags', 'delete tags',
                'view album', 'create album', 'edit album', 'delete album',
                'view video', 'create video', 'edit video', 'delete video',
                'view information', 'create information', 'edit information', 'delete information',
            ],
            'contributor' => [
                'view posts', 'create posts', 'edit posts',
                'view pages', 'create pages', 'edit pages',
                'view menu', 'create menu', 'edit menu',
                'view categories', 'create categories', 'edit categories',
                'view tags', 'create tags', 'edit tags',
                'view album', 'create album', 'edit album',
                'view video', 'create video', 'edit video',
                'view information', 'create information', 'edit information',
            ],
            'user' => [
                'view posts', 'view pages', 'view menu', 'view categories', 
                'view tags', 'view album', 'view video', 'view information',
            ],
        ];

        # Buat dan assign permissions ke roles
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        # Definisikan users utama
        $users = [
            'admin' => [
                'email' => 'admin@kangwendra.com',
                'name' => 'Admin',
                'slug' => 'admin',
            ],
            'editor' => [
                'email' => 'editor@kuliit.com',
                'name' => 'Editor',
                'slug' => 'editor',
            ],
            'contributor' => [
                'email' => 'kontributor@kuliit.com',
                'name' => 'Kontributor',
                'slug' => 'kontributor',
            ],
        ];

        # Buat users utama dan assign roles
        foreach ($users as $role => $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'slug' => $userData['slug'],
                    'password' => Hash::make('123'),
                ]
            );
            $user->assignRole($role);
        }

        # Buat user tambahan dengan role 'user'
        for ($i = 1; $i <= 2; $i++) {
            $randomUser = User::firstOrCreate(
                ['email' => "user{$i}@kuliit.com"],
                [
                    'name' => "User {$i}",
                    'slug' => "user-{$i}",
                    'password' => Hash::make('123'),
                ]
            );
            $randomUser->assignRole('user');
        }
    }
}
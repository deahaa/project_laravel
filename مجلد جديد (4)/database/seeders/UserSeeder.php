<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء مدير
        $adminRole = Role::where('name', 'admin')->first();
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@library.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // إنشاء أمين مكتبة
        $librarianRole = Role::where('name', 'librarian')->first();
        User::create([
            'name' => 'أمين المكتبة',
            'email' => 'librarian@library.com',
            'password' => Hash::make('password'),
            'role_id' => $librarianRole->id,
        ]);

        // إنشاء مستخدم عادي
        $userRole = Role::where('name', 'user')->first();
        User::create([
            'name' => 'مستخدم عادي',
            'email' => 'user@library.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);

        // إنشاء 10 مستخدمين عشوائيين
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "مستخدم {$i}",
                'email' => "user{$i}@library.com",
                'password' => Hash::make('password'),
                'role_id' => $userRole->id,
            ]);
        }
    }
}

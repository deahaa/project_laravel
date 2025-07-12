<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // الحصول على الأدوار
        $admin = Role::where('name', 'admin')->first();
        $librarian = Role::where('name', 'librarian')->first();
        $user = Role::where('name', 'user')->first();

        // الحصول على الصلاحيات
        $manageUsers = Permission::where('name', 'manage-users')->first();
        $manageBooks = Permission::where('name', 'manage-books')->first();
        $manageCategories = Permission::where('name', 'manage-categories')->first();
        $manageBorrowings = Permission::where('name', 'manage-borrowings')->first();
        $borrowBooks = Permission::where('name', 'borrow-books')->first();

        // تعيين الصلاحيات للمدير
        $admin->permissions()->attach([
            $manageUsers->id,
            $manageBooks->id,
            $manageCategories->id,
            $manageBorrowings->id,
            $borrowBooks->id
        ]);

        // تعيين الصلاحيات لأمين المكتبة
        $librarian->permissions()->attach([
            $manageBooks->id,
            $manageCategories->id,
            $manageBorrowings->id,
            $borrowBooks->id
        ]);

        // تعيين الصلاحيات للمستخدم العادي
        $user->permissions()->attach($borrowBooks->id);
    }
}

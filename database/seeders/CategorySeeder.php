<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'العلوم'],
            ['name' => 'الأدب'],
            ['name' => 'التاريخ'],
            ['name' => 'البرمجة'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

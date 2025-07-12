<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {
            for ($i = 1; $i <= 5; $i++) {
                Book::create([
                    'title' => "كتاب {$category->name} رقم {$i}",
                    'author' => "مؤلف {$i}",
                    'isbn' => 'ISBN-' . rand(1000, 9999),
                    'category_id' => $category->id,
                    'quantity' => rand(5, 20),
                    'available_quantity' => rand(5, 20),
                ]);
            }
        }
    }
}

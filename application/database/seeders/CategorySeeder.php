<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Alimentação', 'type' => 'expense'],
            ['name' => 'Transporte', 'type' => 'expense'],
            ['name' => 'Saúde', 'type' => 'expense'],
            ['name' => 'Lazer', 'type' => 'expense'],
            ['name' => 'Moradia', 'type' => 'expense'],
            ['name' => 'Educação', 'type' => 'expense'],
            ['name' => 'Salário', 'type' => 'income'],
            ['name' => 'Freelance', 'type' => 'income'],
            ['name' => 'Investimentos', 'type' => 'income'],
            ['name' => 'Outros', 'type' => 'income'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $categories = Category::all();

        $examples = [
            [
                'description' => 'Compra de energético',
                'amount' => 8.50,
                'type' => 'expense',
                'payment_method' => 'money',
            ],
            [
                'description' => 'Salário mensal',
                'amount' => 3500.00,
                'type' => 'income',
                'payment_method' => 'pix',
            ],
            [
                'description' => 'Conta de luz',
                'amount' => 120.00,
                'type' => 'expense',
                'payment_method' => 'credit_card',
            ],
            [
                'description' => 'Freelance website',
                'amount' => 600.00,
                'type' => 'income',
                'payment_method' => 'pix',
            ],
        ];

        foreach ($examples as $ex) {
            $category = $categories->where('type', $ex['type'])->random();

            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'description' => $ex['description'],
                'amount' => $ex['amount'],
                'transaction_date' => Carbon::now()->subDays(rand(0, 10)),
                'payment_method' => $ex['payment_method'],
                'is_recurring' => false,
            ]);
        }
    }
}

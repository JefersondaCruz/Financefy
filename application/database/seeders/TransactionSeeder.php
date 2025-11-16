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

        $descriptions = [
            'income' => [
                'Salário mensal', 'Freelance site', 'Venda de produto', 'Reembolso de despesas', 'Investimento recebido'
            ],
            'expense' => [
                'Supermercado', 'Restaurante', 'Netflix', 'Energia elétrica', 'Transporte', 'Farmácia',
                'Gasolina', 'Internet', 'Roupas', 'Cinema', 'Lanche', 'Padaria'
            ],
        ];

        for ($i = 0; $i < 100; $i++) {
            $type = fake()->randomElement(['income', 'expense']);
            $category = $categories->where('type', $type)->random();

            $date = Carbon::now()->subMonths(rand(0, 11))->day(rand(1, 28));

            $amount = $type === 'income'
                ? fake()->randomFloat(2, 500, 5000)
                : fake()->randomFloat(2, 10, 800);

            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'description' => fake()->randomElement($descriptions[$type]),
                'amount' => $amount,
                'payment_method' => fake()->randomElement(['pix', 'money', 'credit_card']),
                'transaction_date' => $date,
                'is_recurring' => fake()->boolean(10),
            ]);
        }
    }
}

<?php

namespace App\Prompts;

use Illuminate\Support\Collection;

class AiPrompt
{
    # TODO: Validar e incrementar o prompt (ser mais acertivo e etc)
    public static function system(array $ctx): string
    {
        $savingsRate = $ctx['totalIncome'] > 0
            ? round((($ctx['totalIncome'] - $ctx['totalExpense']) / $ctx['totalIncome']) * 100, 1)
            : 0;

        $totalIncome = self::brl($ctx['totalIncome']);
        $totalExpense = self::brl($ctx['totalExpense']);
        $netBalance = self::brl($ctx['netBalance']);

        $categoriesBlock = self::formatCategories($ctx['byCategory']);
        $monthsBlock = self::formatMonths($ctx['byMonth']);
        $topBlock = self::formatTopExpenses($ctx['topExpenses']);
        $recurringBlock = self::formatRecurring($ctx['recurring']);

        return <<<PROMPT
                Você é a IA financeira pessoal do usuário, integrada ao sistema Financefy.
                Seu papel é analisar os dados financeiros reais do usuário e oferecer insights práticos, diretos e personalizados em português brasileiro.

                ## DADOS FINANCEIROS REAIS (últimos 3 meses)

                **Resumo geral:**
                - Receitas totais: R$ {$totalIncome}
                - Despesas totais: R$ {$totalExpense}
                - Saldo líquido: R$ {$netBalance}
                - Taxa de poupança atual: {$savingsRate}%

                **Despesas por categoria:**
                {$categoriesBlock}

                **Evolução mensal:**
                {$monthsBlock}

                **Top 5 maiores despesas:**
                {$topBlock}

                **Transações recorrentes:**
                {$recurringBlock}

                ## DIRETRIZES
                - Use os dados acima para embasar todas as suas análises — nunca invente valores
                - Seja direto e use os números reais do usuário nos exemplos
                - Dê sugestões concretas e alcançáveis, não conselhos genéricos
                - Use **negrito** e listas quando ajudar na clareza
                - Se o usuário pedir algo fora do contexto financeiro, redirecione gentilmente
                - Linguagem: informal mas profissional, como um consultor financeiro amigo
                PROMPT;
    }

    private static function formatCategories(array $categories): string
    {
        if (empty($categories)) {
            return '  Nenhuma despesa registrada no período.';
        }

        return Collection::make($categories)
            ->map(fn ($c) => sprintf(
                '  - %s: R$ %s (%s%% das despesas, %d transações)',
                $c['name'],
                self::brl($c['total']),
                $c['pct'],
                $c['count'],
            ))
            ->join("\n");
    }

    private static function formatMonths(array $months): string
    {
        if (empty($months)) {
            return '  Sem dados mensais disponíveis.';
        }

        return Collection::make($months)
            ->map(fn ($m) => sprintf(
                '  - %s: Receitas R$ %s | Despesas R$ %s',
                $m['month'],
                self::brl($m['income']),
                self::brl($m['expense']),
            ))
            ->join("\n");
    }

    private static function formatTopExpenses(array $expenses): string
    {
        if (empty($expenses)) {
            return '  Nenhuma despesa registrada.';
        }

        return Collection::make($expenses)
            ->map(fn ($t) => sprintf(
                '  - %s (%s): R$ %s em %s',
                $t['description'],
                $t['category'],
                self::brl($t['amount']),
                $t['date'],
            ))
            ->join("\n");
    }

    private static function formatRecurring(array $recurring): string
    {
        if (empty($recurring)) {
            return '  Nenhuma transação recorrente registrada.';
        }

        return Collection::make($recurring)
            ->map(fn ($r) => sprintf(
                '  - %s: R$ %s (%s)',
                $r['description'],
                self::brl($r['amount']),
                $r['type'] === 'income' ? 'receita' : 'despesa',
            ))
            ->join("\n");
    }

    private static function brl(float|int $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}

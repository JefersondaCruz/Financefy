<?php

namespace App\Prompts;

class AiPrompt
{
    /**
     * Build the system prompt with the user's real financial context.
     */
    public static function system(array $ctx): string
    {
        $categoriesBlock = self::formatCategories($ctx['byCategory']);
        $monthsBlock     = self::formatMonths($ctx['byMonth']);
        $topBlock        = self::formatTopExpenses($ctx['topExpenses']);
        $recurringBlock  = self::formatRecurring($ctx['recurring']);

        $savingsRate = $ctx['totalIncome'] > 0
            ? round((($ctx['totalIncome'] - $ctx['totalExpense']) / $ctx['totalIncome']) * 100, 1)
            : 0;

        $totalIncome  = number_format($ctx['totalIncome'],  2, ',', '.');
        $totalExpense = number_format($ctx['totalExpense'], 2, ',', '.');
        $netBalance   = number_format($ctx['netBalance'],   2, ',', '.');

        return <<<PROMPT
Você é a IA financeira pessoal do usuário, integrada ao sistema Finflow.
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

    // ── Formatters ─────────────────────────────────────────────────────────

    private static function formatCategories(array $categories): string
    {
        if (empty($categories)) {
            return '  Nenhuma despesa registrada no período.';
        }

        return collect($categories)->map(function ($c) {
            $total = number_format($c['total'], 2, ',', '.');
            return "  - {$c['name']}: R$ {$total} ({$c['pct']}% das despesas, {$c['count']} transações)";
        })->join("\n");
    }

    private static function formatMonths(array $months): string
    {
        if (empty($months)) {
            return '  Sem dados mensais disponíveis.';
        }

        return collect($months)->map(function ($m) {
            $income  = number_format($m['income'],  2, ',', '.');
            $expense = number_format($m['expense'], 2, ',', '.');
            return "  - {$m['month']}: Receitas R$ {$income} | Despesas R$ {$expense}";
        })->join("\n");
    }

    private static function formatTopExpenses(array $expenses): string
    {
        if (empty($expenses)) {
            return '  Nenhuma despesa registrada.';
        }

        return collect($expenses)->map(function ($t) {
            $amount = number_format($t['amount'], 2, ',', '.');
            return "  - {$t['description']} ({$t['category']}): R$ {$amount} em {$t['date']}";
        })->join("\n");
    }

    private static function formatRecurring(array $recurring): string
    {
        if (empty($recurring)) {
            return '  Nenhuma transação recorrente registrada.';
        }

        return collect($recurring)->map(function ($r) {
            $amount = number_format($r['amount'], 2, ',', '.');
            $type   = $r['type'] === 'income' ? 'receita' : 'despesa';
            return "  - {$r['description']}: R$ {$amount} ({$type})";
        })->join("\n");
    }
}

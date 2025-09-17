<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Validar ano e mês, ou usar a data atual como padrão.
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $selectedDate = Carbon::create($year, $month, 1);

        // 1. Saldo Inicial de todas as contas
        $totalInitialBalance = $user->accounts()->sum('initial_balance');

        // 2. Soma de todas as transações até o FINAL do mês selecionado
        $transactionsUpToMonth = $user->transactions()
            ->where('date', '<=', $selectedDate->endOfMonth()->toDateString())
            ->get();

        $currentBalance = $transactionsUpToMonth->reduce(function ($carry, $transaction) {
            if ($transaction->type === 'income') {
                return $carry + $transaction->value;
            }
            return $carry - $transaction->value;
        }, $totalInitialBalance);

        // 3. Receitas e Despesas APENAS do mês selecionado
        $monthlyTransactions = $user->transactions()
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        $monthlyIncomes = (clone $monthlyTransactions)->where('type', 'income')->sum('value');
        $monthlyExpenses = (clone $monthlyTransactions)->where('type', 'expense')->sum('value');


        return Inertia::render('Dashboard', [
            'stats' => [
                'current_balance' => $currentBalance,
                'monthly_incomes' => $monthlyIncomes,
                'monthly_expenses' => $monthlyExpenses,
                'credit_card_expenses' => 0, // Placeholder
            ],
            'filters' => [
                'year' => (int)$year,
                'month' => (int)$month,
            ],
        ]);
    }
}

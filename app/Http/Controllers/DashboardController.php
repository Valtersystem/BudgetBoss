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

        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);

        $selectedDate = Carbon::create($year, $month, 1);
        $startOfSelectedMonth = $selectedDate->copy()->startOfMonth();
        $endOfSelectedMonth = $selectedDate->copy()->endOfMonth();

        $monthlyStandardTransactions = $user->transactions()
            ->where('is_fixed', false)
            ->whereBetween('date', [$startOfSelectedMonth, $endOfSelectedMonth]);

        $monthlyStandardIncomes = (clone $monthlyStandardTransactions)->where('type', 'income')->sum('value');
        $monthlyStandardExpenses = (clone $monthlyStandardTransactions)->where('type', 'expense')->sum('value');

        $fixedTransactions = $user->transactions()
            ->where('is_fixed', true)
            ->where('date', '<=', $endOfSelectedMonth)
            ->get();

        $monthlyFixedIncomes = $fixedTransactions->where('type', 'income')->sum('value');
        $monthlyFixedExpenses = $fixedTransactions->where('type', 'expense')->sum('value');

        $monthlyIncomes = $monthlyStandardIncomes + $monthlyFixedIncomes;
        $monthlyExpenses = $monthlyStandardExpenses + $monthlyFixedExpenses;

        $totalInitialBalance = $user->accounts()->where('dashboard', true)->sum('initial_balance');

        $nonFixedTransactionsUpToMonth = $user->transactions()
            ->where('is_fixed', false)
            ->where('date', '<=', $endOfSelectedMonth)
            ->get();

        $balanceFromNonFixed = $nonFixedTransactionsUpToMonth->reduce(function ($carry, $transaction) {
            return $transaction->type === 'income' ? $carry + $transaction->value : $carry - $transaction->value;
        }, 0);


        $balanceFromFixed = $fixedTransactions->reduce(function ($carry, $transaction) use ($endOfSelectedMonth) {
            $startDate = Carbon::parse($transaction->date);

            $monthsCount = $startDate->diffInMonths($endOfSelectedMonth) + 1;

            if ($monthsCount > 0) {
                $totalValue = $transaction->value * $monthsCount;
                return $transaction->type === 'income' ? $carry + $totalValue : $carry - $totalValue;
            }

            return $carry;
        }, 0);

        $currentBalance = $totalInitialBalance + $balanceFromNonFixed + $balanceFromFixed;

        return Inertia::render('Dashboard', [
            'stats' => [
                'current_balance' => $currentBalance,
                'monthly_incomes' => $monthlyIncomes,
                'monthly_expenses' => $monthlyExpenses,
                'credit_card_expenses' => 0,
            ],
            'filters' => [
                'year' => (int)$year,
                'month' => (int)$month,
            ],
        ]);
    }
}

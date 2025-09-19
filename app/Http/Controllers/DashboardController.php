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

        // Standard (non-fixed) transactions for the selected month
        $monthlyStandardTransactions = $user->transactions()
            ->where('is_fixed', false)
            ->whereBetween('date', [$startOfSelectedMonth, $endOfSelectedMonth]);

        $monthlyStandardIncomes = (clone $monthlyStandardTransactions)->where('type', 'income')->sum('value');
        $monthlyStandardExpenses = (clone $monthlyStandardTransactions)->where('type', 'expense')->sum('value');

        // Fixed transactions up to the end of the selected month
        $fixedTransactions = $user->transactions()
            ->where('is_fixed', true)
            ->where('date', '<=', $endOfSelectedMonth)
            ->get();

        // Monthly income/expense from fixed transactions active in the selected month
        $monthlyFixedIncomes = $fixedTransactions->where('type', 'income')->sum('value');
        $monthlyFixedExpenses = $fixedTransactions->where('type', 'expense')->sum('value');

        $monthlyIncomes = $monthlyStandardIncomes + $monthlyFixedIncomes;
        $monthlyExpenses = $monthlyStandardExpenses + $monthlyFixedExpenses;

        // --- CURRENT BALANCE CALCULATION ---

        $totalInitialBalance = $user->accounts()->where('dashboard', true)->sum('initial_balance');

        // Cumulative balance from all non-fixed transactions up to the end of the month
        $nonFixedTransactionsUpToMonth = $user->transactions()
            ->where('is_fixed', false)
            ->where('date', '<=', $endOfSelectedMonth)
            ->get();

        $balanceFromNonFixed = $nonFixedTransactionsUpToMonth->reduce(function ($carry, $transaction) {
            return $transaction->type === 'income' ? $carry + $transaction->value : $carry - $transaction->value;
        }, 0);

        // Cumulative balance from all fixed transactions
        $balanceFromFixed = $fixedTransactions->reduce(function ($carry, $transaction) use ($endOfSelectedMonth) {
            $startDate = Carbon::parse($transaction->date);

            // --- FIX START ---
            // The previous logic using diffInMonths was unreliable.
            // This new logic accurately calculates the number of months a transaction has been active.
            if ($startDate->isAfter($endOfSelectedMonth)) {
                return $carry;
            }

            $monthsCount = ($endOfSelectedMonth->year - $startDate->year) * 12 + ($endOfSelectedMonth->month - $startDate->month) + 1;
            // --- FIX END ---

            $totalValue = $transaction->value * $monthsCount;

            return $transaction->type === 'income' ? $carry + $totalValue : $carry - $totalValue;
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

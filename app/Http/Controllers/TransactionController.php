<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        $type = $request->input('type', 'all'); // 'all', 'income', or 'expense'
        $includeFixed = $request->boolean('include_fixed', true);

        $query = $request->user()->transactions()->with(['account', 'category', 'tag']);

        if (in_array($type, ['income', 'expense'])) {
            $query->where('type', $type);
        }

        $query->where(function ($q) use ($year, $month, $includeFixed) {
            // Base query for the selected month and year
            $q->whereYear('date', $year)->whereMonth('date', $month);

            // If includeFixed is true, also get fixed transactions from the past
            if ($includeFixed) {
                $endDate = Carbon::create($year, $month)->endOfMonth();
                $q->orWhere(function($subQ) use ($endDate) {
                    $subQ->where('is_fixed', true)
                         ->where('date', '<=', $endDate);
                });
            }
        });

        $transactions = $query->latest('date')->paginate(20)->withQueryString();

        // --- Stats Calculation ---
        $statsBaseQuery = $request->user()->transactions();
        if ($includeFixed) {
            $endDate = Carbon::create($year, $month)->endOfMonth();
             $statsBaseQuery->where(function ($q) use ($year, $month, $endDate) {
                $q->where(function ($subQ) use ($year, $month) {
                    $subQ->whereYear('date', $year)->whereMonth('date', $month);
                })
                ->orWhere(function ($subQ) use ($endDate) {
                    $subQ->where('is_fixed', true)->where('date', '<=', $endDate);
                });
            });
        } else {
            $statsBaseQuery->whereYear('date', $year)->whereMonth('date', $month);
        }

        // Income stats
        $incomesQuery = (clone $statsBaseQuery)->where('type', 'income');
        $receivedIncomes = (clone $incomesQuery)->where('is_paid', true)->sum('value');
        $outstandingIncomes = (clone $incomesQuery)->where('is_paid', false)->sum('value');

        // Expense stats
        $expensesQuery = (clone $statsBaseQuery)->where('type', 'expense');
        $paidExpenses = (clone $expensesQuery)->where('is_paid', true)->sum('value');
        $outstandingExpenses = (clone $expensesQuery)->where('is_paid', false)->sum('value');


        $accounts = $request->user()->accounts()->get(['id', 'name']);
        $categories = $request->user()->categories()->get(['id', 'name', 'type', 'color', 'icon']);
        $tags = $request->user()->tags()->get(['id', 'name']);

        return Inertia::render('transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'tags' => $tags,
            'stats' => [
                'receivedIncomes' => $receivedIncomes,
                'outstandingIncomes' => $outstandingIncomes,
                'totalIncomes' => $receivedIncomes + $outstandingIncomes,
                'paidExpenses' => $paidExpenses,
                'outstandingExpenses' => $outstandingExpenses,
                'totalExpenses' => $paidExpenses + $outstandingExpenses,
            ],
            'filters' => [
                'year' => (int)$year,
                'month' => (int)$month,
                'type' => $type,
                'include_fixed' => $includeFixed,
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!empty($data['is_recurring']) && empty($data['is_fixed'])) {
            $this->createRecurringTransactions($data, $user);
        } else {
            $data['installments'] = null;
            $data['installment_period'] = null;
            if (!empty($data['is_fixed'])) {
                $data['is_recurring'] = false;
            }
            $user->transactions()->create($data);
        }

        return redirect()->back();
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        $data = $request->validated();
        $user = $request->user();


        if (!empty($data['is_recurring'])) {
            $transaction->delete();
            $this->createRecurringTransactions($data, $user);
        } else {
            $data['installments'] = null;
            $data['installment_period'] = null;
            $transaction->update($data);
        }

        return redirect()->back();
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        $transaction->delete();
        return redirect()->back();
    }

    /**
     * Cria uma série de transações recorrentes.
     *
     * @param array $data The validated transaction data.
     * @param \App\Models\User $user The user creating the transaction.
     * @return void
     */
    private function createRecurringTransactions(array $data, User $user): void
    {
        $startDate = Carbon::parse($data['date']);
        $installments = $data['installments'] ?? 1;
        $period = $data['installment_period'] ?? 'months';

        for ($i = 1; $i <= $installments; $i++) {
            $transactionData = $data;
            $transactionData['date'] = $startDate->toDateString();
            $transactionData['description'] = $data['description'] . " ($i/$installments)";

            $transactionData['is_fixed'] = false;
            $transactionData['is_recurring'] = false;
            $transactionData['installments'] = null;
            $transactionData['installment_period'] = null;

            $user->transactions()->create($transactionData);

            switch ($period) {
                case 'days':
                    $startDate->addDay();
                    break;
                case 'weeks':
                    $startDate->addWeek();
                    break;
                case 'months':
                    $startDate->addMonth();
                    break;
                case 'years':
                    $startDate->addYear();
                    break;
            }
        }
    }
}

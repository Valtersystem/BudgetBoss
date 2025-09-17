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

        $transactions = $request->user()->transactions()
            ->with(['account', 'category', 'tag'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->latest('date')
            ->paginate(20);

        $accounts = $request->user()->accounts()->get(['id', 'name']);
        $categories = $request->user()->categories()->get(['id', 'name', 'type', 'color', 'icon']);
        $tags = $request->user()->tags()->get(['id', 'name']);

        return Inertia::render('transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'tags' => $tags,
            'filters' => [
                'year' => (int)$year,
                'month' => (int)$month,
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        if (!empty($data['is_recurring'])) {
            $this->createRecurringTransactions($data, $user);
        } else {
            // Handle single and fixed transactions.
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
            // If the transaction is updated to be recurring,
            // delete the old one and create the new series.
            $transaction->delete();
            $this->createRecurringTransactions($data, $user);
        } else {
            // If it's a simple update (not recurring)
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
     * Creates a series of recurring transactions.
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

            // Reset recurring fields for individual installments
            $transactionData['is_fixed'] = false;
            $transactionData['is_recurring'] = false;
            $transactionData['installments'] = null;
            $transactionData['installment_period'] = null;

            $user->transactions()->create($transactionData);

            // Increment date for the next installment
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

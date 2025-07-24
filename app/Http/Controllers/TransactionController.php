<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Carregar as transações com as suas relações para mostrar os nomes na tabela
        $transactions = $user->transactions()->with(['account', 'category'])->latest('date')->get();

        return Inertia::render('transactions/Index', [
            'transactions' => $transactions,
            // Passar as listas para os menus de seleção no formulário
            'accounts' => $user->accounts()->get(['id', 'name']),
            'categories' => $user->categories()->get(['id', 'name']),
            'tags' => $user->tags()->get(['id', 'name']), // Vamos precisar disto
        ]);
    }

public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0.01',
        'type' => ['required', Rule::in(['income', 'expense'])],
        'date' => 'required|date',
        'paid' => 'required|boolean',
        'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
        'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $user->id)],
        'tags' => 'nullable|array',
        'tags.*' => ['integer', Rule::exists('tags', 'id')->where('user_id', $user->id)],
        'is_recurring' => 'required|boolean',
        'installments' => 'nullable|integer|min:2|required_if:is_recurring,true',
        'frequency' => ['nullable', 'string', Rule::in(['daily', 'weekly', 'monthly', 'yearly']), 'required_if:is_recurring,true'],
    ]);

    if (!$validated['is_recurring']) {
        $transaction = $user->transactions()->create($validated);
        if (!empty($validated['tags'])) {
            $transaction->tags()->sync($validated['tags']);
        }
    } else {
        $recurrenceId = time() . $user->id; // Cria um ID único para o grupo
        $startDate = Carbon::parse($validated['date']);

        for ($i = 1; $i <= $validated['installments']; $i++) {
            $transactionDate = $startDate->copy();

            if ($i > 1) {
                switch ($validated['frequency']) {
                    case 'daily':
                        $transactionDate->addDays($i - 1);
                        break;
                    case 'weekly':
                        $transactionDate->addWeeks($i - 1);
                        break;
                    case 'monthly':
                        $transactionDate->addMonthsNoOverflow($i - 1);
                        break;
                    case 'yearly':
                        $transactionDate->addYearsNoOverflow($i - 1);
                        break;
                }
            }

            $transaction = $user->transactions()->create(array_merge($validated, [
                'date' => $transactionDate,
                'recurrence_id' => $recurrenceId,
                'current_installment' => $i,
            ]));

            if (!empty($validated['tags'])) {
                $transaction->tags()->sync($validated['tags']);
            }
        }
    }

    return redirect()->back()->with('success', 'Transaction created successfully.');
}

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        $user = $request->user();

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => ['required', Rule::in(['income', 'expense'])],
            'date' => 'required|date',
            'paid' => 'required|boolean',
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $user->id)],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $user->id)],
            'tags' => 'nullable|array',
            'tags.*' => ['integer', Rule::exists('tags', 'id')->where('user_id', $user->id)],
        ]);

        $transaction->update($validated);

        // Sincroniza as tags, mesmo que o array venha vazio (para remover as que já existiam)
        $transaction->tags()->sync($validated['tags'] ?? []);

        return redirect()->back()->with('success', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction deleted.');
    }
}

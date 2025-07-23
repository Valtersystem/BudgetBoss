<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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
            'tags' => 'nullable|array', // Validação para as tags
            'tags.*' => ['integer', Rule::exists('tags', 'id')->where('user_id', $user->id)],
        ]);

        $transaction = $user->transactions()->create($validated);

        // Se foram enviadas tags, sincroniza-as com a transação
        if (!empty($validated['tags'])) {
            $transaction->tags()->sync($validated['tags']);
        }

        return redirect()->back()->with('success', 'Transaction created.');
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

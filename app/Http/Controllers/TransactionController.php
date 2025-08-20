<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        // Carregamos as transações com os relacionamentos para evitar N+1 queries
        $transactions = $request->user()->transactions()
            ->with(['account', 'category', 'tag'])
            ->latest('date')
            ->paginate(20);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
        ]);
    }

    public function create(Request $request)
    {
        // Passamos os dados necessários para os dropdowns do formulário
        return Inertia::render('Transactions/Create', [
            'accounts' => $request->user()->accounts()->get(['id', 'name']),
            'categories' => $request->user()->categories()->get(['id', 'name']),
            'tags' => $request->user()->tags()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'is_paid' => 'required|boolean',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_id' => 'nullable|exists:tags,id',
            'notes' => 'nullable|string',
            // Validação para recorrência (opcional, pode ser adicionada depois)
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('transactions.index')->with('success', 'Lançamento criado com sucesso.');
    }

    // O método show() pode ser usado para uma página de detalhes da transação
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction->load(['account', 'category', 'tag']),
        ]);
    }

    // O método edit() renderiza o formulário de edição
    public function edit(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'accounts' => $request->user()->accounts()->get(['id', 'name']),
            'categories' => $request->user()->categories()->get(['id', 'name']),
            'tags' => $request->user()->tags()->get(['id', 'name']),
        ]);
    }


    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'type' => 'required|in:income,expense',
            'is_paid' => 'required|boolean',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'tag_id' => 'nullable|exists:tags,id',
            'notes' => 'nullable|string',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Lançamento atualizado.');
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Lançamento eliminado.');
    }
}

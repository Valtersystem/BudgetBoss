<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = auth()->user()->accounts()->latest()->get();

        return Inertia::render('settings/accounts/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'initial_balance' => 'required|numeric',
            'color' => 'required|string|size:7', // #RRGGBB
            'include_in_dashboard' => 'required|boolean',
        ]);

        $request->user()->accounts()->create($validated);

        return redirect()->back()->with('success', 'Account created.');
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'initial_balance' => 'required|numeric',
            'color' => 'required|string|size:7',
            'include_in_dashboard' => 'required|boolean',
        ]);

        $account->update($validated);

        return redirect()->back()->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        // Adicionar lógica futura para verificar se a conta tem transações antes de apagar
        // Por agora, vamos apenas apagar.

        $account->delete();

        return redirect()->back()->with('success', 'Account deleted.');
    }
}

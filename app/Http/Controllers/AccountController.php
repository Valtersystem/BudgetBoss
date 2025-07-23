<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $accounts = $user->accounts()->with(['financialInstitution', 'accountType'])->latest()->get();

        return Inertia::render('accounts/Index', [
            'accounts' => $accounts,
            'financial_institutions' => $user->financialInstitutions()->get(['id', 'name']),
            'account_types' => $user->accountTypes()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric',
            'color' => 'required|string|size:7',
            'include_in_dashboard' => 'required|boolean',
            // Novas regras de validação
            'financial_institution_id' => ['nullable', Rule::exists('financial_institutions', 'id')->where('user_id', $user->id)],
            'account_type_id' => ['nullable', Rule::exists('account_types', 'id')->where('user_id', $user->id)],
        ]);

        $user->accounts()->create($validated);
        return redirect()->back()->with('success', 'Account created.');
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric',
            'color' => 'required|string|size:7',
            'include_in_dashboard' => 'required|boolean',
            'financial_institution_id' => ['nullable', Rule::exists('financial_institutions', 'id')->where('user_id', $user->id)],
            'account_type_id' => ['nullable', Rule::exists('account_types', 'id')->where('user_id', $user->id)],
        ]);

        $account->update($validated);
        return redirect()->back()->with('success', 'Account updated.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);
        $account->delete();
        return redirect()->back()->with('success', 'Account deleted.');
    }

    public function toggle(Account $account)
    {
        $this->authorize('update', $account);

        $account->update([
            'include_in_dashboard' => !$account->include_in_dashboard,
        ]);

        return redirect()->back();
    }
}

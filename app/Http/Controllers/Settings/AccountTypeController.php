<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/account-types/Index', [
            'account_types' => auth()->user()->accountTypes()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $request->user()->accountTypes()->create($validated);
        return redirect()->back()->with('success', 'Account type created.');
    }

    public function update(Request $request, AccountType $accountType)
    {
        $this->authorize('update', $accountType);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $accountType->update($validated);
        return redirect()->back()->with('success', 'Account type updated.');
    }

    public function destroy(AccountType $accountType)
    {
        $this->authorize('delete', $accountType);
        $accountType->delete();
        return redirect()->back()->with('success', 'Account type deleted.');
    }
}

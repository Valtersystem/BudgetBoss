<?php

namespace App\Http\Controllers;

use App\Models\BankInstitution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BankInstitutionController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $bankInstitutions = $request->user()->bankInstitutions()->latest()->get();

        return Inertia::render('Settings/BankInstitutions/Index', [
            'bankInstitutions' => $bankInstitutions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $request->user()->bankInstitutions()->create($validated);

        return redirect()->back()->with('success', 'Instituição bancária criada.');
    }

    public function update(Request $request, BankInstitution $bankInstitution)
    {
        $this->authorize('update', $bankInstitution);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $bankInstitution->update($validated);

        return redirect()->back()->with('success', 'Instituição bancária atualizada.');
    }

    public function destroy(BankInstitution $bankInstitution)
    {
        $this->authorize('delete', $bankInstitution);

        $bankInstitution->delete();

        return redirect()->back()->with('success', 'Instituição bancária eliminada.');
    }
}

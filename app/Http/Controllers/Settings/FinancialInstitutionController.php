<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\FinancialInstitution;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancialInstitutionController extends Controller
{
    public function index()
    {
        return Inertia::render('settings/financial-institutions/Index', [
            'institutions' => auth()->user()->financialInstitutions()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $request->user()->financialInstitutions()->create($validated);
        return redirect()->back()->with('success', 'Institution created.');
    }

    public function update(Request $request, FinancialInstitution $financialInstitution)
    {
        $this->authorize('update', $financialInstitution);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $financialInstitution->update($validated);
        return redirect()->back()->with('success', 'Institution updated.');
    }

    public function destroy(FinancialInstitution $financialInstitution)
    {
        $this->authorize('delete', $financialInstitution);
        $financialInstitution->delete();
        return redirect()->back()->with('success', 'Institution deleted.');
    }
}

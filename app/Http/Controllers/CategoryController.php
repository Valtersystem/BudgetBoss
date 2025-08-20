<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Settings/Categories/Index', [
            'categories' => $request->user()->categories()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $request->user()->categories()->create($validated);

        return redirect()->back()->with('success', 'Categoria criada.');
    }

    public function update(Request $request, Category $category)
    {
        if ($request->user()->cannot('update', $category)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Categoria atualizada.');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($request->user()->cannot('delete', $category)) {
            abort(403);
        }

        $category->delete();

        return redirect()->back()->with('success', 'Categoria eliminada.');
    }
}

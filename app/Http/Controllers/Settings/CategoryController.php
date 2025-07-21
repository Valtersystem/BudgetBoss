<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;



class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = auth()->user()->categories()->latest()->get();

        // Retorna a página Vue, passando as categorias como 'props'
        return Inertia::render('settings/categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['income', 'expense'])],
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7', // Validação para cor hexadecimal
        ]);

        $request->user()->categories()->create($validated);

        return redirect()->back()->with('success', 'Category created.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Garante que o usuário só pode editar a própria categoria
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['income', 'expense'])],
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Garante que o usuário só pode deletar a própria categoria
        $this->authorize('delete', $category);

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }
}

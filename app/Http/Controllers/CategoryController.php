<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('settings/Categories/Index', [
            'categories' => Auth::user()->categories()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'type' => ['required', Rule::in(['expense', 'income'])],
        ]);

        $request->user()->categories()->create($validated);

        return redirect()->route('categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if ($request->user()->id !== $category->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'type' => ['required', Rule::in(['expense', 'income'])],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        if ($request->user()->id !== $category->user_id) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.index');
    }
}

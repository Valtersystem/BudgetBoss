<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Settings/Tags/Index', [
            'tags' => $request->user()->tags()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $request->user()->tags()->create($validated);

        return redirect()->back()->with('success', 'Tag criada.');
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== $request->user()->id) {
             abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
        ]);

        $tag->update($validated);

        return redirect()->back()->with('success', 'Tag atualizada.');
    }

    public function destroy(Request $request, Tag $tag)
    {
        if ($tag->user_id !== $request->user()->id) {
             abort(403);
        }

        $tag->delete();

        return redirect()->back()->with('success', 'Tag eliminada.');
    }
}

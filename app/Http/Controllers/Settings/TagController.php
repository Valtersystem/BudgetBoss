<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    public function index()
    {
        $tags = auth()->user()->tags()->latest()->get();

        return Inertia::render('settings/tags/Index', [
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $request->user()->tags()->create($validated);

        return redirect()->back()->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update($validated);

        return redirect()->back()->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);

        $tag->delete();

        return redirect()->back()->with('success', 'Tag deleted.');
    }
}

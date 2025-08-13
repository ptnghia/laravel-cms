<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('author')->latest()->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
        ]);

        Page::create($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function show(Page $page)
    {
        return view('admin.pages.show', compact('page'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
        ]);

        $page->update($validated);
        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function duplicate(Page $page)
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (Copy)';
        $newPage->status = 'draft';
        $newPage->save();

        return redirect()->route('admin.pages.edit', $newPage)->with('success', 'Page duplicated successfully.');
    }

    public function updateStatus(Request $request, Page $page)
    {
        $request->validate(['status' => 'required|in:draft,published']);
        $page->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Page status updated successfully.']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft',
            'pages' => 'required|array',
        ]);

        $pages = Page::whereIn('id', $request->pages);

        switch ($request->action) {
            case 'delete':
                $pages->delete();
                $message = 'Pages deleted successfully.';
                break;
            case 'publish':
                $pages->update(['status' => 'published']);
                $message = 'Pages published successfully.';
                break;
            case 'draft':
                $pages->update(['status' => 'draft']);
                $message = 'Pages moved to draft successfully.';
                break;
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $pages = Page::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->with('author')
            ->limit(10)
            ->get();

        return response()->json($pages);
    }

    public function quickCreate(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $page = Page::create([
            'title' => $request->title,
            'content' => '',
            'status' => 'draft',
            'author_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'page' => $page,
            'redirect' => route('admin.pages.edit', $page),
        ]);
    }

    public function autosave(Request $request, Page $page = null)
    {
        $data = $request->only(['title', 'content']);
        
        if ($page) {
            $page->update($data);
        } else {
            $data['status'] = 'draft';
            $data['author_id'] = auth()->id();
            $page = Page::create($data);
        }

        return response()->json([
            'success' => true,
            'page_id' => $page->id,
            'saved_at' => now()->toISOString(),
        ]);
    }
}

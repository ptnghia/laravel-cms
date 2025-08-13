<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['author', 'categories'])
            ->latest()
            ->paginate(20);

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $post = Post::create($validated);

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,scheduled',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'categories' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $post->update($validated);

        if ($request->has('categories')) {
            $post->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Duplicate a post.
     */
    public function duplicate(Post $post)
    {
        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->status = 'draft';
        $newPost->save();

        // Copy relationships
        $newPost->categories()->sync($post->categories->pluck('id'));
        $newPost->tags()->sync($post->tags->pluck('id'));

        return redirect()->route('admin.posts.edit', $newPost)
            ->with('success', 'Post duplicated successfully.');
    }

    /**
     * Update post status.
     */
    public function updateStatus(Request $request, Post $post)
    {
        $request->validate([
            'status' => 'required|in:draft,published,scheduled',
        ]);

        $post->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Post status updated successfully.',
        ]);
    }

    /**
     * Handle bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,publish,draft',
            'posts' => 'required|array',
            'posts.*' => 'exists:posts,id',
        ]);

        $posts = Post::whereIn('id', $request->posts);

        switch ($request->action) {
            case 'delete':
                $posts->delete();
                $message = 'Posts deleted successfully.';
                break;
            case 'publish':
                $posts->update(['status' => 'published']);
                $message = 'Posts published successfully.';
                break;
            case 'draft':
                $posts->update(['status' => 'draft']);
                $message = 'Posts moved to draft successfully.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Search posts.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $posts = Post::where('title', 'like', "%{$query}%")
            ->orWhere('content', 'like', "%{$query}%")
            ->with(['author', 'categories'])
            ->limit(10)
            ->get();

        return response()->json($posts);
    }

    /**
     * Quick create post.
     */
    public function quickCreate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $post = Post::create([
            'title' => $request->title,
            'content' => '',
            'status' => 'draft',
            'author_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'post' => $post,
            'redirect' => route('admin.posts.edit', $post),
        ]);
    }

    /**
     * Auto-save post.
     */
    public function autosave(Request $request, Post $post = null)
    {
        $data = $request->only(['title', 'content', 'excerpt']);
        
        if ($post) {
            $post->update($data);
        } else {
            $data['status'] = 'draft';
            $data['author_id'] = auth()->id();
            $post = Post::create($data);
        }

        return response()->json([
            'success' => true,
            'post_id' => $post->id,
            'saved_at' => now()->toISOString(),
        ]);
    }
}

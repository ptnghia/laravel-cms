<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['author', 'category', 'tags']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by author
        if ($request->has('author_id')) {
            $query->where('author_id', $request->get('author_id'));
        }

        // Filter by post type
        if ($request->has('post_type')) {
            $query->where('post_type', $request->get('post_type'));
        }

        // Filter by tags
        if ($request->has('tags')) {
            $tags = explode(',', $request->get('tags'));
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $posts = $query->paginate($perPage);

        return $this->paginatedResponse(
            $posts,
            'Posts retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            // Set author
            $data['author_id'] = $request->user()->id;

            $post = Post::create($data);

            // Handle tags
            if (!empty($data['tags'])) {
                $tagIds = [];
                foreach ($data['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['name' => $tagName],
                        ['slug' => Str::slug($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }
                $post->tags()->sync($tagIds);
            }

            $post->load(['author', 'category', 'tags', 'featuredImage']);

            return $this->createdResponse(
                new PostResource($post),
                'Post created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create post: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $post = Post::with(['author', 'category', 'tags', 'comments' => function ($query) {
            $query->where('status', 'approved')->with('user');
        }])->find($id);

        if (!$post) {
            return $this->notFoundResponse('Post not found');
        }

        // Increment view count
        $post->increment('view_count');

        return $this->successResponse(
            new PostResource($post),
            'Post retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,published,scheduled',
            'post_type' => 'nullable|string|max:50',
            'published_at' => 'nullable|date',
            'scheduled_at' => 'nullable|date|after:now',
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'seo_meta' => 'nullable|array',
            'meta_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug ?: Str::slug($request->title);

        // Ensure slug is unique (excluding current post)
        $originalSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $post->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'category_id' => $request->category_id,
            'status' => $request->status,
            'post_type' => $request->post_type ?: 'post',
            'published_at' => $request->status === 'published' ? ($request->published_at ?: $post->published_at ?: now()) : null,
            'scheduled_at' => $request->scheduled_at,
            'featured_image_id' => $request->featured_image_id,
            'gallery' => $request->gallery,
            'seo_meta' => $request->seo_meta,
            'meta_data' => $request->meta_data,
        ]);

        // Update tags
        if ($request->has('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName], [
                    'slug' => Str::slug($tagName),
                ]);
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        $post->load(['author', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => ['post' => $post],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Publish a post.
     */
    public function publish(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully',
            'data' => ['post' => $post],
        ]);
    }

    /**
     * Unpublish a post.
     */
    public function unpublish(string $id): JsonResponse
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        $post->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post unpublished successfully',
            'data' => ['post' => $post],
        ]);
    }

    /**
     * Duplicate a post.
     */
    public function duplicate(string $id): JsonResponse
    {
        $originalPost = Post::with('tags')->find($id);

        if (!$originalPost) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Create duplicate
        $duplicatePost = $originalPost->replicate();
        $duplicatePost->title = $originalPost->title . ' (Copy)';
        $duplicatePost->slug = $originalPost->slug . '-copy-' . time();
        $duplicatePost->status = 'draft';
        $duplicatePost->published_at = null;
        $duplicatePost->view_count = 0;
        $duplicatePost->comment_count = 0;
        $duplicatePost->rating_avg = 0;
        $duplicatePost->rating_count = 0;
        $duplicatePost->author_id = request()->user()->id;
        $duplicatePost->save();

        // Duplicate tags
        if ($originalPost->tags->count() > 0) {
            $duplicatePost->tags()->sync($originalPost->tags->pluck('id'));
        }

        $duplicatePost->load(['author', 'category', 'tags']);

        return response()->json([
            'success' => true,
            'message' => 'Post duplicated successfully',
            'data' => ['post' => $duplicatePost],
        ], 201);
    }

    /**
     * Get post by slug.
     */
    public function getBySlug(string $slug): JsonResponse
    {
        $post = Post::with(['author', 'category', 'tags', 'comments' => function ($query) {
            $query->where('status', 'approved')->with('user');
        }])->where('slug', $slug)->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Increment view count
        $post->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => ['post' => $post],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->get('color'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'usage_count');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $tags = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'tags' => $tags->items(),
                'pagination' => [
                    'current_page' => $tags->currentPage(),
                    'last_page' => $tags->lastPage(),
                    'per_page' => $tags->perPage(),
                    'total' => $tags->total(),
                    'from' => $tags->firstItem(),
                    'to' => $tags->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $tag = Tag::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'color' => $data['color'],
                'description' => $data['description'] ?? null,
                'usage_count' => 0,
            ]);

            return $this->createdResponse(
                new TagResource($tag),
                'Tag created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create tag: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $tag = Tag::with(['posts' => function ($query) {
            $query->where('status', 'published')->with('author');
        }])->find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['tag' => $tag],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags,slug,' . $id,
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Generate slug if not provided
        $slug = $request->slug ?: Str::slug($request->name);

        // Ensure slug is unique (excluding current tag)
        $originalSlug = $slug;
        $counter = 1;
        while (Tag::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $tag->update([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'data' => ['tag' => $tag],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        // Detach from all posts
        $tag->posts()->detach();

        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
        ]);
    }

    /**
     * Get tag by slug.
     */
    public function getBySlug(string $slug): JsonResponse
    {
        $tag = Tag::with(['posts' => function ($query) {
            $query->where('status', 'published')->with('author');
        }])->where('slug', $slug)->first();

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => 'Tag not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['tag' => $tag],
        ]);
    }

    /**
     * Get popular tags.
     */
    public function popular(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 50);

        $tags = Tag::where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['tags' => $tags],
        ]);
    }

    /**
     * Search tags by name.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $request->get('q');
        $limit = min($request->get('limit', 10), 50);

        $tags = Tag::where('name', 'like', "%{$query}%")
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['tags' => $tags],
        ]);
    }
}

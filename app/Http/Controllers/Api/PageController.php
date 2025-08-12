<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Page::query();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by template
        if ($request->has('template')) {
            $query->where('template', $request->get('template'));
        }

        // Filter by parent
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->get('parent_id'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $pages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'pages' => $pages->items(),
                'pagination' => [
                    'current_page' => $pages->currentPage(),
                    'last_page' => $pages->lastPage(),
                    'per_page' => $pages->perPage(),
                    'total' => $pages->total(),
                    'from' => $pages->firstItem(),
                    'to' => $pages->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'required|string',
            'template' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'parent_id' => 'nullable|exists:pages,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'page_builder_data' => 'nullable|array',
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

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Page::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $page = Page::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'template' => $request->template ?: 'default',
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? ($request->published_at ?: now()) : null,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->boolean('is_active', true),
            'page_builder_data' => $request->page_builder_data,
            'seo_meta' => $request->seo_meta,
            'meta_data' => $request->meta_data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully',
            'data' => ['page' => $page],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $page = Page::with(['parent', 'children', 'comments' => function ($query) {
            $query->where('status', 'approved')->with('user');
        }])->find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['page' => $page],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $id,
            'content' => 'required|string',
            'template' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'parent_id' => 'nullable|exists:pages,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'page_builder_data' => 'nullable|array',
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

        // Ensure slug is unique (excluding current page)
        $originalSlug = $slug;
        $counter = 1;
        while (Page::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $page->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'template' => $request->template ?: 'default',
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? ($request->published_at ?: $page->published_at ?: now()) : null,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->boolean('is_active', true),
            'page_builder_data' => $request->page_builder_data,
            'seo_meta' => $request->seo_meta,
            'meta_data' => $request->meta_data,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
            'data' => ['page' => $page],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        // Check if page has children
        if ($page->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete page with child pages',
            ], 400);
        }

        $page->delete();

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }

    /**
     * Get page by slug.
     */
    public function getBySlug(string $slug): JsonResponse
    {
        $page = Page::with(['parent', 'children', 'comments' => function ($query) {
            $query->where('status', 'approved')->with('user');
        }])->where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['page' => $page],
        ]);
    }

    /**
     * Get page tree structure.
     */
    public function tree(): JsonResponse
    {
        $pages = Page::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['pages' => $pages],
        ]);
    }
}

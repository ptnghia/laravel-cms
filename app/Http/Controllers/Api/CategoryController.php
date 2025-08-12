<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::with(['parent', 'children']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
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
        $categories = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $category = Category::create($data);
            $category->load(['parent', 'children']);

            return $this->createdResponse(
                new CategoryResource($category),
                'Category created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create category: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'posts' => function ($query) {
            $query->where('status', 'published')->with('author');
        }])->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['category' => $category],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'seo_meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if trying to set parent as itself or its descendant
        if ($request->parent_id && $this->isDescendant($category->id, $request->parent_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set parent as itself or its descendant',
            ], 400);
        }

        // Generate slug if not provided
        $slug = $request->slug ?: Str::slug($request->name);

        // Ensure slug is unique (excluding current category)
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'image' => $request->image,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?: 0,
            'is_active' => $request->boolean('is_active', true),
            'seo_meta' => $request->seo_meta,
        ]);

        $category->load(['parent', 'children']);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => ['category' => $category],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with child categories',
            ], 400);
        }

        // Check if category has posts
        if ($category->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with posts',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get category tree structure.
     */
    public function tree(): JsonResponse
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['categories' => $categories],
        ]);
    }

    /**
     * Get category by slug.
     */
    public function getBySlug(string $slug): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'posts' => function ($query) {
            $query->where('status', 'published')->with('author');
        }])->where('slug', $slug)->where('is_active', true)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['category' => $category],
        ]);
    }

    /**
     * Check if a category is descendant of another.
     */
    private function isDescendant(int $categoryId, int $potentialParentId): bool
    {
        if ($categoryId === $potentialParentId) {
            return true;
        }

        $category = Category::find($categoryId);
        $descendants = $this->getDescendants($category);

        return in_array($potentialParentId, $descendants);
    }

    /**
     * Get all descendant IDs of a category.
     */
    private function getDescendants(Category $category): array
    {
        $descendants = [];

        foreach ($category->children as $child) {
            $descendants[] = $child->id;
            $descendants = array_merge($descendants, $this->getDescendants($child));
        }

        return $descendants;
    }
}

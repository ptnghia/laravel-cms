<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Http\Resources\MenuResource;
use App\Http\Resources\MenuItemResource;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::with(['menuItems.children']);

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', $request->get('location'));
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $menus = $query->paginate($perPage);

        return $this->paginatedResponse(
            $menus,
            'Menus retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $menu = Menu::create([
                'name' => $data['name'],
                'location' => $data['location'],
                'is_active' => $data['is_active'],
            ]);

            // Create menu items if provided
            if (!empty($data['menu_items'])) {
                foreach ($data['menu_items'] as $itemData) {
                    MenuItem::create([
                        'menu_id' => $menu->id,
                        'title' => $itemData['title'],
                        'url' => $itemData['url'],
                        'target' => $itemData['target'] ?? '_self',
                        'icon' => $itemData['icon'] ?? null,
                        'css_class' => $itemData['css_class'] ?? null,
                        'parent_id' => $itemData['parent_id'] ?? null,
                        'sort_order' => $itemData['sort_order'] ?? 0,
                        'is_active' => $itemData['is_active'] ?? true,
                    ]);
                }
            }

            $menu->load(['menuItems.children']);

            return $this->createdResponse(
                new MenuResource($menu),
                'Menu created successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create menu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $menu = Menu::with(['menuItems.children.children'])->find($id);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        return $this->successResponse(
            new MenuResource($menu),
            'Menu retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:menus,name,' . $id,
            'location' => 'required|string|max:100|unique:menus,location,' . $id,
            'menu_items' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        $menu->update([
            'name' => $request->name,
            'location' => Str::slug($request->location, '_'),
            'menu_items' => $request->menu_items,
            'is_active' => $request->boolean('is_active', $menu->is_active),
        ]);

        $menu->load(['menuItems.children']);

        return $this->updatedResponse(
            new MenuResource($menu),
            'Menu updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        // Delete all menu items first
        $menu->allMenuItems()->delete();
        $menu->delete();

        return $this->deletedResponse('Menu deleted successfully');
    }

    /**
     * Get menu by location.
     */
    public function getByLocation(string $location): JsonResponse
    {
        $menu = Menu::with(['menuItems.children.children'])
            ->where('location', $location)
            ->where('is_active', true)
            ->first();

        if (!$menu) {
            return $this->notFoundResponse('Menu not found for location: ' . $location);
        }

        return $this->successResponse(
            new MenuResource($menu),
            'Menu retrieved successfully'
        );
    }

    /**
     * Update menu structure (reorder items).
     */
    public function updateStructure(Request $request, string $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        // Update menu items structure
        foreach ($request->items as $itemData) {
            MenuItem::where('id', $itemData['id'])
                ->where('menu_id', $menu->id)
                ->update([
                    'parent_id' => $itemData['parent_id'],
                    'sort_order' => $itemData['sort_order'],
                ]);
        }

        $menu->load(['menuItems.children.children']);

        return $this->updatedResponse(
            new MenuResource($menu),
            'Menu structure updated successfully'
        );
    }

    /**
     * Add menu item to menu.
     */
    public function addItem(Request $request, string $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        // Verify parent belongs to same menu
        if ($request->parent_id) {
            $parent = MenuItem::find($request->parent_id);
            if (!$parent || $parent->menu_id !== $menu->id) {
                return $this->errorResponse('Invalid parent menu item', 400);
            }
        }

        $menuItem = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => $request->title,
            'url' => $request->url,
            'parent_id' => $request->parent_id,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        $menuItem->load(['children']);

        return $this->createdResponse(
            new MenuItemResource($menuItem),
            'Menu item added successfully'
        );
    }

    /**
     * Remove menu item from menu.
     */
    public function removeItem(string $menuId, string $itemId): JsonResponse
    {
        $menu = Menu::find($menuId);

        if (!$menu) {
            return $this->notFoundResponse('Menu not found');
        }

        $menuItem = MenuItem::where('id', $itemId)
            ->where('menu_id', $menu->id)
            ->first();

        if (!$menuItem) {
            return $this->notFoundResponse('Menu item not found');
        }

        // Delete all children first
        $menuItem->descendants()->delete();
        $menuItem->delete();

        return $this->deletedResponse('Menu item removed successfully');
    }

    /**
     * Get all available menu locations.
     */
    public function locations(): JsonResponse
    {
        $locations = [
            'header' => 'Header Menu',
            'footer' => 'Footer Menu',
            'sidebar' => 'Sidebar Menu',
            'mobile' => 'Mobile Menu',
            'primary' => 'Primary Navigation',
            'secondary' => 'Secondary Navigation',
            'admin' => 'Admin Menu',
            'user' => 'User Menu',
        ];

        return $this->successResponse(
            ['locations' => $locations],
            'Menu locations retrieved successfully'
        );
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        // Filter by group
        if ($request->has('group')) {
            $query->where('group', $request->get('group'));
        }

        // Filter by public/private
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'group');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder)->orderBy('key', 'asc');

        // Pagination
        $perPage = min($request->get('per_page', 50), 200);
        $settings = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings->items(),
                'pagination' => [
                    'current_page' => $settings->currentPage(),
                    'last_page' => $settings->lastPage(),
                    'per_page' => $settings->perPage(),
                    'total' => $settings->total(),
                    'from' => $settings->firstItem(),
                    'to' => $settings->lastItem(),
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
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
            'type' => 'required|in:string,integer,boolean,json,array',
            'group' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate value based on type
        $value = $request->value;
        switch ($request->type) {
            case 'integer':
                if (!is_numeric($value)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be a valid integer',
                    ], 422);
                }
                break;
            case 'boolean':
                if (!in_array(strtolower($value), ['0', '1', 'true', 'false'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be a valid boolean (0, 1, true, false)',
                    ], 422);
                }
                break;
            case 'json':
            case 'array':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be valid JSON',
                    ], 422);
                }
                break;
        }

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $value,
            'type' => $request->type,
            'group' => $request->group,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully',
            'data' => ['setting' => $setting],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['setting' => $setting],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate value based on existing type
        $value = $request->value;
        switch ($setting->type) {
            case 'integer':
                if (!is_numeric($value)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be a valid integer',
                    ], 422);
                }
                break;
            case 'boolean':
                if (!in_array(strtolower($value), ['0', '1', 'true', 'false'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be a valid boolean (0, 1, true, false)',
                    ], 422);
                }
                break;
            case 'json':
            case 'array':
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Value must be valid JSON',
                    ], 422);
                }
                break;
        }

        $setting->update([
            'value' => $value,
            'description' => $request->description ?: $setting->description,
            'is_public' => $request->boolean('is_public', $setting->is_public),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => ['setting' => $setting],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $setting = Setting::find($id);

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        // Prevent deleting critical system settings
        $criticalSettings = [
            'site_name', 'site_description', 'timezone', 'date_format', 'time_format',
            'posts_per_page', 'allow_comments', 'moderate_comments'
        ];

        if (in_array($setting->key, $criticalSettings)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete critical system setting',
            ], 403);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Get settings grouped by category.
     */
    public function groups(): JsonResponse
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $groupedSettings = $settings->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => ['settings' => $groupedSettings],
        ]);
    }

    /**
     * Get public settings only.
     */
    public function public(): JsonResponse
    {
        $settings = Setting::where('is_public', true)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(function ($setting) {
                // Cast value based on type
                $value = $setting->value;
                switch ($setting->type) {
                    case 'integer':
                        $value = (int) $value;
                        break;
                    case 'boolean':
                        $value = in_array(strtolower($value), ['1', 'true']);
                        break;
                    case 'json':
                    case 'array':
                        $value = json_decode($value, true);
                        break;
                }
                return [$setting->key => $value];
            });

        return response()->json([
            'success' => true,
            'data' => ['settings' => $settings],
        ]);
    }

    /**
     * Bulk update settings.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $updatedSettings = [];

        foreach ($request->settings as $settingData) {
            $setting = Setting::where('key', $settingData['key'])->first();

            if ($setting) {
                // Validate value based on type
                $value = $settingData['value'];
                $isValid = true;

                switch ($setting->type) {
                    case 'integer':
                        $isValid = is_numeric($value);
                        break;
                    case 'boolean':
                        $isValid = in_array(strtolower($value), ['0', '1', 'true', 'false']);
                        break;
                    case 'json':
                    case 'array':
                        json_decode($value);
                        $isValid = json_last_error() === JSON_ERROR_NONE;
                        break;
                }

                if ($isValid) {
                    $setting->update(['value' => $value]);
                    $updatedSettings[] = $setting;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($updatedSettings) . ' settings updated successfully',
            'data' => ['settings' => $updatedSettings],
        ]);
    }
}

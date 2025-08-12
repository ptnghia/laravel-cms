<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

abstract class BaseApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Default pagination limit.
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum pagination limit.
     */
    protected int $maxPerPage = 100;

    /**
     * Get pagination parameters from request.
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'per_page' => min(
                $request->get('per_page', $this->defaultPerPage),
                $this->maxPerPage
            ),
            'page' => $request->get('page', 1),
        ];
    }

    /**
     * Get sorting parameters from request.
     */
    protected function getSortingParams(Request $request, string $defaultSort = 'created_at', string $defaultOrder = 'desc'): array
    {
        return [
            'sort_by' => $request->get('sort_by', $defaultSort),
            'sort_order' => $request->get('sort_order', $defaultOrder),
        ];
    }

    /**
     * Apply search filters to query.
     */
    protected function applySearch($query, Request $request, array $searchFields = []): void
    {
        if ($request->has('search') && !empty($searchFields)) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }
    }

    /**
     * Apply filters to query.
     */
    protected function applyFilters($query, Request $request, array $filterFields = []): void
    {
        foreach ($filterFields as $field => $operator) {
            if ($request->has($field)) {
                $value = $request->get($field);
                
                if (is_string($operator)) {
                    $query->where($field, $operator, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }
    }

    /**
     * Apply sorting to query.
     */
    protected function applySorting($query, Request $request, string $defaultSort = 'created_at', string $defaultOrder = 'desc'): void
    {
        $params = $this->getSortingParams($request, $defaultSort, $defaultOrder);
        $query->orderBy($params['sort_by'], $params['sort_order']);
    }

    /**
     * Get common meta data for responses.
     */
    protected function getCommonMeta(Request $request): array
    {
        return [
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'api_version' => config('app.api_version', '1.0'),
            'server_time' => now()->toISOString(),
        ];
    }

    /**
     * Validate model exists or return 404.
     */
    protected function findModelOrFail($model, $id, string $message = 'Resource not found')
    {
        $instance = $model::find($id);
        
        if (!$instance) {
            abort(404, $message);
        }
        
        return $instance;
    }

    /**
     * Check if user has permission for action.
     */
    protected function checkPermission(string $permission): bool
    {
        $user = request()->user();
        
        if (!$user) {
            return false;
        }

        return $user->hasPermission($permission) || 
               $user->hasRole('super_admin');
    }

    /**
     * Get allowed includes from request.
     */
    protected function getAllowedIncludes(Request $request, array $allowedIncludes = []): array
    {
        $includes = $request->get('include', '');
        
        if (empty($includes)) {
            return [];
        }

        $requestedIncludes = explode(',', $includes);
        
        return array_intersect($requestedIncludes, $allowedIncludes);
    }

    /**
     * Apply includes to query.
     */
    protected function applyIncludes($query, array $includes): void
    {
        if (!empty($includes)) {
            $query->with($includes);
        }
    }

    /**
     * Get validation error response.
     */
    protected function getValidationErrorResponse(\Illuminate\Contracts\Validation\Validator $validator): JsonResponse
    {
        return $this->validationErrorResponse(
            $validator->errors()->toArray(),
            'Dữ liệu không hợp lệ'
        );
    }

    /**
     * Log API activity.
     */
    protected function logActivity(string $action, $model = null, array $data = []): void
    {
        $user = request()->user();
        
        \Log::info('API Activity', [
            'action' => $action,
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'model' => $model ? get_class($model) : null,
            'model_id' => $model?->id ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Handle bulk operations.
     */
    protected function handleBulkOperation(Request $request, string $action, $model): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:' . (new $model)->getTable() . ',id',
        ]);

        if ($validator->fails()) {
            return $this->getValidationErrorResponse($validator);
        }

        $ids = $request->get('ids');
        $count = 0;

        switch ($action) {
            case 'delete':
                $count = $model::whereIn('id', $ids)->delete();
                break;
            case 'activate':
                $count = $model::whereIn('id', $ids)->update(['status' => 'active']);
                break;
            case 'deactivate':
                $count = $model::whereIn('id', $ids)->update(['status' => 'inactive']);
                break;
        }

        $this->logActivity("bulk_{$action}", null, ['ids' => $ids, 'count' => $count]);

        return $this->successResponse(
            ['affected_count' => $count],
            "Bulk {$action} completed successfully"
        );
    }
}

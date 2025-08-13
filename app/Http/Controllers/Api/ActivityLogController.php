<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityLogController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by response code
        if ($request->filled('response_code')) {
            $query->where('response_code', $request->response_code);
        }

        // Filter by IP address
        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        // Search in description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 15), 100);
        $logs = $query->paginate($perPage);

        return ActivityLogResource::collection($logs);
    }

    /**
     * Display the specified activity log.
     */
    public function show(ActivityLog $activityLog): ActivityLogResource
    {
        $activityLog->load('user');
        
        return new ActivityLogResource($activityLog);
    }

    /**
     * Remove the specified activity log.
     */
    public function destroy(ActivityLog $activityLog): JsonResponse
    {
        $activityLog->delete();

        return $this->successResponse(
            null,
            'Activity log deleted successfully'
        );
    }

    /**
     * Clean up old activity logs.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365',
            'keep_errors' => 'boolean',
        ]);

        $days = $request->get('days', 30);
        $keepErrors = $request->get('keep_errors', true);
        $cutoffDate = now()->subDays($days);

        $query = ActivityLog::where('created_at', '<', $cutoffDate);

        // Keep error logs if requested
        if ($keepErrors) {
            $query->where('response_code', '<', 400);
        }

        $deletedCount = $query->count();
        $query->delete();

        return $this->successResponse([
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->toISOString(),
            'kept_errors' => $keepErrors,
        ], "Cleaned up {$deletedCount} activity logs older than {$days} days");
    }

    /**
     * Get activity log statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $fromDate = now()->subDays($days);

        $stats = [
            'total_requests' => ActivityLog::where('created_at', '>=', $fromDate)->count(),
            'unique_users' => ActivityLog::where('created_at', '>=', $fromDate)
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),
            'unique_ips' => ActivityLog::where('created_at', '>=', $fromDate)
                ->distinct('ip_address')
                ->count(),
            'error_rate' => $this->getErrorRate($fromDate),
            'top_endpoints' => $this->getTopEndpoints($fromDate),
            'top_users' => $this->getTopUsers($fromDate),
            'response_codes' => $this->getResponseCodeStats($fromDate),
            'hourly_distribution' => $this->getHourlyDistribution($fromDate),
        ];

        return $this->successResponse($stats, 'Activity log statistics retrieved successfully');
    }

    /**
     * Get error rate percentage.
     */
    protected function getErrorRate(\Carbon\Carbon $fromDate): float
    {
        $total = ActivityLog::where('created_at', '>=', $fromDate)->count();
        
        if ($total === 0) {
            return 0;
        }

        $errors = ActivityLog::where('created_at', '>=', $fromDate)
            ->where('response_code', '>=', 400)
            ->count();

        return round(($errors / $total) * 100, 2);
    }

    /**
     * Get top endpoints by request count.
     */
    protected function getTopEndpoints(\Carbon\Carbon $fromDate, int $limit = 10): array
    {
        return ActivityLog::where('created_at', '>=', $fromDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get top users by request count.
     */
    protected function getTopUsers(\Carbon\Carbon $fromDate, int $limit = 10): array
    {
        return ActivityLog::where('created_at', '>=', $fromDate)
            ->whereNotNull('user_id')
            ->with('user:id,name,email')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'user_id' => $log->user_id,
                    'user_name' => $log->user->name ?? 'Unknown',
                    'user_email' => $log->user->email ?? 'Unknown',
                    'count' => $log->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get response code statistics.
     */
    protected function getResponseCodeStats(\Carbon\Carbon $fromDate): array
    {
        return ActivityLog::where('created_at', '>=', $fromDate)
            ->selectRaw('response_code, COUNT(*) as count')
            ->groupBy('response_code')
            ->orderBy('response_code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->response_code => $item->count];
            })
            ->toArray();
    }

    /**
     * Get hourly request distribution.
     */
    protected function getHourlyDistribution(\Carbon\Carbon $fromDate): array
    {
        return ActivityLog::where('created_at', '>=', $fromDate)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(function ($item) {
                return [sprintf('%02d:00', $item->hour) => $item->count];
            })
            ->toArray();
    }
}

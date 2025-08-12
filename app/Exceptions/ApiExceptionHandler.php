<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Render API exception response.
     */
    public static function render(Request $request, Throwable $e): JsonResponse
    {
        // Handle validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
                'timestamp' => now()->toISOString(),
                'status_code' => 422,
                'error' => [
                    'code' => 422,
                    'type' => 'VALIDATION_ERROR',
                ],
            ], 422);
        }

        // Handle authentication exceptions
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa xác thực',
                'timestamp' => now()->toISOString(),
                'status_code' => 401,
                'error' => [
                    'code' => 401,
                    'type' => 'AUTHENTICATION_ERROR',
                ],
            ], 401);
        }

        // Handle authorization exceptions
        if ($e instanceof AccessDeniedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền truy cập',
                'timestamp' => now()->toISOString(),
                'status_code' => 403,
                'error' => [
                    'code' => 403,
                    'type' => 'AUTHORIZATION_ERROR',
                ],
            ], 403);
        }

        // Handle model not found exceptions
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài nguyên',
                'timestamp' => now()->toISOString(),
                'status_code' => 404,
                'error' => [
                    'code' => 404,
                    'type' => 'RESOURCE_NOT_FOUND',
                    'model' => class_basename($e->getModel()),
                ],
            ], 404);
        }

        // Handle not found exceptions
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint không tồn tại',
                'timestamp' => now()->toISOString(),
                'status_code' => 404,
                'error' => [
                    'code' => 404,
                    'type' => 'ENDPOINT_NOT_FOUND',
                ],
            ], 404);
        }

        // Handle HTTP exceptions
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: self::getDefaultMessage($statusCode),
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
                'error' => [
                    'code' => $statusCode,
                    'type' => self::getErrorType($statusCode),
                ],
            ], $statusCode);
        }

        // Handle general exceptions
        $statusCode = 500;
        $message = config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống';

        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'status_code' => $statusCode,
            'error' => [
                'code' => $statusCode,
                'type' => 'INTERNAL_ERROR',
            ],
        ];

        // Add debug information in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ];
        }

        // Log the exception
        \Log::error('API Exception', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json($response, $statusCode);
    }

    /**
     * Get default message for status code.
     */
    private static function getDefaultMessage(int $statusCode): string
    {
        switch ($statusCode) {
            case 400: return 'Yêu cầu không hợp lệ';
            case 401: return 'Chưa xác thực';
            case 403: return 'Không có quyền truy cập';
            case 404: return 'Không tìm thấy';
            case 405: return 'Phương thức không được phép';
            case 422: return 'Dữ liệu không hợp lệ';
            case 429: return 'Quá nhiều yêu cầu';
            case 500: return 'Lỗi hệ thống';
            case 503: return 'Dịch vụ không khả dụng';
            default: return 'Lỗi không xác định';
        }
    }

    /**
     * Get error type for status code.
     */
    private static function getErrorType(int $statusCode): string
    {
        switch ($statusCode) {
            case 400: return 'BAD_REQUEST';
            case 401: return 'UNAUTHORIZED';
            case 403: return 'FORBIDDEN';
            case 404: return 'NOT_FOUND';
            case 405: return 'METHOD_NOT_ALLOWED';
            case 422: return 'VALIDATION_ERROR';
            case 429: return 'RATE_LIMIT_EXCEEDED';
            case 500: return 'INTERNAL_ERROR';
            case 503: return 'SERVICE_UNAVAILABLE';
            default: return 'UNKNOWN_ERROR';
        }
    }
}

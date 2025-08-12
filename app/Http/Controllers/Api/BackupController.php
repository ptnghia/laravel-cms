<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BackupController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of backups.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Backup::with(['creator']);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('file_name', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $backups = $query->paginate($perPage);

        return $this->paginatedResponse(
            $backups,
            'Backups retrieved successfully'
        );
    }

    /**
     * Create a new backup.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:database,files,full',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator->errors()->toArray(),
                'Dữ liệu không hợp lệ'
            );
        }

        try {
            $type = $request->type;
            $description = $request->description ?? "Backup created at " . now()->format('Y-m-d H:i:s');

            $fileName = $this->generateBackupFileName($type);
            $filePath = storage_path("app/backups/{$fileName}");

            // Ensure backup directory exists
            if (!File::exists(dirname($filePath))) {
                File::makeDirectory(dirname($filePath), 0755, true);
            }

            switch ($type) {
                case 'database':
                    $this->createDatabaseBackup($filePath);
                    break;
                case 'files':
                    $this->createFilesBackup($filePath);
                    break;
                case 'full':
                    $this->createFullBackup($filePath);
                    break;
            }

            // Record backup in database
            $backup = Backup::create([
                'file_name' => $fileName,
                'file_path' => $filePath,
                'type' => $type,
                'description' => $description,
                'file_size' => File::size($filePath),
                'created_by' => $request->user()->id,
                'created_at' => now(),
            ]);

            return $this->createdResponse(
                $backup,
                'Backup created successfully'
            );
        } catch (\Exception $e) {
            Log::error('Backup creation failed', [
                'error' => $e->getMessage(),
                'type' => $request->type,
                'user_id' => $request->user()->id,
            ]);

            return $this->errorResponse(
                'Failed to create backup: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Download a backup file.
     */
    public function download(string $id)
    {
        $backup = Backup::find($id);

        if (!$backup) {
            return $this->notFoundResponse('Backup not found');
        }

        if (!File::exists($backup->file_path)) {
            return $this->errorResponse('Backup file not found on disk', 404);
        }

        return response()->download($backup->file_path, $backup->file_name);
    }

    /**
     * Delete a backup.
     */
    public function destroy(string $id): JsonResponse
    {
        $backup = Backup::find($id);

        if (!$backup) {
            return $this->notFoundResponse('Backup not found');
        }

        try {
            // Delete file from disk
            if (File::exists($backup->file_path)) {
                File::delete($backup->file_path);
            }

            // Delete record from database
            $backup->delete();

            return $this->deletedResponse('Backup deleted successfully');
        } catch (\Exception $e) {
            Log::error('Backup deletion failed', [
                'error' => $e->getMessage(),
                'backup_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to delete backup: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get backup statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_backups' => Backup::count(),
            'total_size' => Backup::sum('file_size'),
            'total_size_human' => $this->formatBytes(Backup::sum('file_size')),
            'by_type' => Backup::select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type')
                ->toArray(),
            'latest_backup' => Backup::latest()->first(['file_name', 'type', 'created_at']),
            'oldest_backup' => Backup::oldest()->first(['file_name', 'type', 'created_at']),
        ];

        return $this->successResponse(
            ['statistics' => $stats],
            'Backup statistics retrieved successfully'
        );
    }

    /**
     * Generate backup file name.
     */
    private function generateBackupFileName(string $type): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $siteName = str_replace(' ', '_', config('app.name', 'laravel_cms'));

        return "{$siteName}_{$type}_backup_{$timestamp}.sql";
    }

    /**
     * Create database backup.
     */
    private function createDatabaseBackup(string $filePath): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if ($config['driver'] === 'sqlite') {
            // For SQLite, just copy the database file
            $dbPath = database_path($config['database']);
            File::copy($dbPath, $filePath);
        } else {
            // For MySQL/PostgreSQL, use mysqldump/pg_dump
            $this->createSqlDump($filePath, $config);
        }
    }

    /**
     * Create files backup.
     */
    private function createFilesBackup(string $filePath): void
    {
        // Create a zip archive of important files
        $zip = new \ZipArchive();

        if ($zip->open($filePath, \ZipArchive::CREATE) === TRUE) {
            // Add storage/app/public directory
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/');

            // Add uploads if exists
            if (File::exists(public_path('uploads'))) {
                $this->addDirectoryToZip($zip, public_path('uploads'), 'uploads/');
            }

            $zip->close();
        } else {
            throw new \Exception('Could not create backup archive');
        }
    }

    /**
     * Create full backup (database + files).
     */
    private function createFullBackup(string $filePath): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($filePath, \ZipArchive::CREATE) === TRUE) {
            // Add database backup
            $dbBackupPath = storage_path('app/temp_db_backup.sql');
            $this->createDatabaseBackup($dbBackupPath);
            $zip->addFile($dbBackupPath, 'database.sql');

            // Add files
            $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage/');

            if (File::exists(public_path('uploads'))) {
                $this->addDirectoryToZip($zip, public_path('uploads'), 'uploads/');
            }

            $zip->close();

            // Clean up temp database backup
            if (File::exists($dbBackupPath)) {
                File::delete($dbBackupPath);
            }
        } else {
            throw new \Exception('Could not create full backup archive');
        }
    }

    /**
     * Add directory to zip archive recursively.
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $directory, string $prefix = ''): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $prefix . substr($filePath, strlen($directory) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Create SQL dump for MySQL/PostgreSQL.
     */
    private function createSqlDump(string $filePath, array $config): void
    {
        if ($config['driver'] === 'mysql') {
            $command = sprintf(
                'mysqldump -h%s -u%s -p%s %s > %s',
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $filePath
            );
        } elseif ($config['driver'] === 'pgsql') {
            $command = sprintf(
                'pg_dump -h %s -U %s -d %s > %s',
                $config['host'],
                $config['username'],
                $config['database'],
                $filePath
            );
        } else {
            throw new \Exception('Unsupported database driver for backup');
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database backup command failed');
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

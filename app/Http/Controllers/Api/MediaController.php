<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Media::with(['folder']);

        // Filter by folder
        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->get('folder_id'));
        }

        // Filter by file type
        if ($request->has('file_type')) {
            $query->where('file_type', $request->get('file_type'));
        }

        // Filter by mime type
        if ($request->has('mime_type')) {
            $query->where('mime_type', $request->get('mime_type'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%")
                  ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $media = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'media' => $media->items(),
                'pagination' => [
                    'current_page' => $media->currentPage(),
                    'last_page' => $media->lastPage(),
                    'per_page' => $media->perPage(),
                    'total' => $media->total(),
                    'from' => $media->firstItem(),
                    'to' => $media->lastItem(),
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
            'file' => 'required|file|max:10240', // 10MB max
            'folder_id' => 'nullable|exists:media_folders,id',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;

        // Determine file type
        $fileType = $this->getFileType($mimeType);

        // Store file
        $path = $file->storeAs('media/' . date('Y/m'), $filename, 'public');

        // Create media record
        $media = Media::create([
            'original_name' => $originalName,
            'filename' => $filename,
            'path' => $path,
            'file_type' => $fileType,
            'mime_type' => $mimeType,
            'size' => $size,
            'folder_id' => $request->folder_id,
            'alt_text' => $request->alt_text,
            'caption' => $request->caption,
            'uploaded_by' => $request->user()->id,
        ]);

        // Generate thumbnails for images
        if ($fileType === 'image') {
            $this->generateThumbnails($media);
        }

        $media->load('folder');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => ['media' => $media],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $media = Media::with(['folder'])->find($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['media' => $media],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $media = Media::find($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'folder_id' => 'nullable|exists:media_folders,id',
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $media->update([
            'folder_id' => $request->folder_id,
            'alt_text' => $request->alt_text,
            'caption' => $request->caption,
        ]);

        $media->load('folder');

        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully',
            'data' => ['media' => $media],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $media = Media::find($id);

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found',
            ], 404);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        // Delete thumbnails if exist
        if ($media->thumbnails) {
            foreach ($media->thumbnails as $thumbnail) {
                if (Storage::disk('public')->exists($thumbnail)) {
                    Storage::disk('public')->delete($thumbnail);
                }
            }
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Get file type based on mime type.
     */
    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (in_array($mimeType, ['application/pdf'])) {
            return 'document';
        } else {
            return 'other';
        }
    }

    /**
     * Generate thumbnails for images.
     */
    private function generateThumbnails(Media $media): void
    {
        // This is a placeholder for thumbnail generation
        // In a real application, you would use an image processing library
        // like Intervention Image to generate thumbnails

        $thumbnails = [
            'small' => 'thumbnails/small_' . $media->filename,
            'medium' => 'thumbnails/medium_' . $media->filename,
            'large' => 'thumbnails/large_' . $media->filename,
        ];

        $media->update(['thumbnails' => $thumbnails]);
    }
}

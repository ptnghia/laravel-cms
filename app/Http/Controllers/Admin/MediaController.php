<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::latest()->paginate(20);
        return view('admin.media.index', compact('media'));
    }

    public function create()
    {
        return view('admin.media.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'file_type' => 'required|string',
            'file_size' => 'required|integer',
        ]);

        Media::create($validated);
        return redirect()->route('admin.media.index')->with('success', 'Media uploaded successfully.');
    }

    public function show(Media $media)
    {
        return view('admin.media.show', compact('media'));
    }

    public function edit(Media $media)
    {
        return view('admin.media.edit', compact('media'));
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'alt_text' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $media->update($validated);
        return redirect()->route('admin.media.index')->with('success', 'Media updated successfully.');
    }

    public function destroy(Media $media)
    {
        $media->delete();
        return redirect()->route('admin.media.index')->with('success', 'Media deleted successfully.');
    }

    public function upload(Request $request)
    {
        // Handle file upload logic here
        return response()->json(['success' => true, 'message' => 'File uploaded successfully.']);
    }

    public function bulkUpload(Request $request)
    {
        // Handle bulk file upload logic here
        return response()->json(['success' => true, 'message' => 'Files uploaded successfully.']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'media' => 'required|array',
        ]);

        if ($request->action === 'delete') {
            Media::whereIn('id', $request->media)->delete();
            $message = 'Media files deleted successfully.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function download(Media $media)
    {
        // Handle file download logic here
        return response()->download(storage_path('app/' . $media->file_path));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $media = Media::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($media);
    }

    public function createFolder(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        // Handle folder creation logic here
        return response()->json(['success' => true, 'message' => 'Folder created successfully.']);
    }

    public function move(Request $request, Media $media)
    {
        $request->validate(['folder' => 'required|string']);
        // Handle file move logic here
        return response()->json(['success' => true, 'message' => 'File moved successfully.']);
    }

    public function rename(Request $request, Media $media)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $media->update(['name' => $request->name]);
        return response()->json(['success' => true, 'message' => 'File renamed successfully.']);
    }
}

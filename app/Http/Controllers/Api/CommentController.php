<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with(['user', 'commentable']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by commentable type
        if ($request->has('commentable_type')) {
            $query->where('commentable_type', $request->get('commentable_type'));
        }

        // Filter by commentable id
        if ($request->has('commentable_id')) {
            $query->where('commentable_id', $request->get('commentable_id'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $comments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'comments' => $comments->items(),
                'pagination' => [
                    'current_page' => $comments->currentPage(),
                    'last_page' => $comments->lastPage(),
                    'per_page' => $comments->perPage(),
                    'total' => $comments->total(),
                    'from' => $comments->firstItem(),
                    'to' => $comments->lastItem(),
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
            'content' => 'required|string|max:2000',
            'commentable_type' => 'required|in:App\Models\Post,App\Models\Page',
            'commentable_id' => 'required|integer',
            'parent_id' => 'nullable|exists:comments,id',
            'author_name' => 'required_without:user_id|string|max:255',
            'author_email' => 'required_without:user_id|email|max:255',
            'author_website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify commentable exists
        $commentableClass = $request->commentable_type;
        $commentable = $commentableClass::find($request->commentable_id);

        if (!$commentable) {
            return response()->json([
                'success' => false,
                'message' => 'Commentable resource not found',
            ], 404);
        }

        // Check if parent comment exists and belongs to same commentable
        if ($request->parent_id) {
            $parentComment = Comment::find($request->parent_id);
            if (!$parentComment ||
                $parentComment->commentable_type !== $request->commentable_type ||
                $parentComment->commentable_id != $request->commentable_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parent comment',
                ], 400);
            }
        }

        $comment = Comment::create([
            'content' => $request->content,
            'commentable_type' => $request->commentable_type,
            'commentable_id' => $request->commentable_id,
            'parent_id' => $request->parent_id,
            'user_id' => $request->user() ? $request->user()->id : null,
            'author_name' => $request->author_name ?: ($request->user() ? $request->user()->name : null),
            'author_email' => $request->author_email ?: ($request->user() ? $request->user()->email : null),
            'author_website' => $request->author_website,
            'status' => 'pending', // Default to pending for moderation
        ]);

        $comment->load(['user', 'commentable']);

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => ['comment' => $comment],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $comment = Comment::with(['user', 'commentable', 'replies.user'])->find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['comment' => $comment],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'status' => 'nullable|in:pending,approved,spam',
            'author_name' => 'nullable|string|max:255',
            'author_email' => 'nullable|email|max:255',
            'author_website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $comment->update([
            'content' => $request->content,
            'status' => $request->status ?: $comment->status,
            'author_name' => $request->author_name ?: $comment->author_name,
            'author_email' => $request->author_email ?: $comment->author_email,
            'author_website' => $request->author_website,
        ]);

        $comment->load(['user', 'commentable']);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => ['comment' => $comment],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        // Delete all replies first
        $comment->replies()->delete();

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(string $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        $comment->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully',
            'data' => ['comment' => $comment],
        ]);
    }

    /**
     * Mark comment as spam.
     */
    public function spam(string $id): JsonResponse
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        $comment->update(['status' => 'spam']);

        return response()->json([
            'success' => true,
            'message' => 'Comment marked as spam',
            'data' => ['comment' => $comment],
        ]);
    }
}

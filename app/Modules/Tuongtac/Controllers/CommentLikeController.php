<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CommentLike;
use App\Modules\Tuongtac\Models\TComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentLikeController extends Controller
{
    /**
     * Toggle like status for a comment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request)
    {
        // Validate request
        $request->validate([
            'comment_id' => 'required|integer',
            'item_id' => 'required|integer',
            'item_code' => 'required|string',
        ]);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ]);
        }
        
        $userId = Auth::id();
        $commentId = $request->comment_id;
        
        // Check if comment exists
        $comment = TComment::find($commentId);
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found'
            ]);
        }
        
        // Check if user already liked this comment
        $existingLike = CommentLike::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->first();
        
        if ($existingLike) {
            // User already liked the comment, so unlike it
            $existingLike->delete();
            $liked = false;
        } else {
            // User hasn't liked the comment yet, so add a like
            CommentLike::create([
                'user_id' => $userId,
                'comment_id' => $commentId
            ]);
            $liked = true;
        }
        
        // Get updated like count
        $likesCount = CommentLike::where('comment_id', $commentId)->count();
        
        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likesCount' => $likesCount
        ]);
    }
    
    /**
     * Get like count for a comment
     *
     * @param int $commentId
     * @return int
     */
    public static function getLikeCount($commentId)
    {
        return CommentLike::where('comment_id', $commentId)->count();
    }
    
    /**
     * Check if user has liked a comment
     *
     * @param int $commentId
     * @param int|null $userId
     * @return bool
     */
    public static function hasUserLiked($commentId, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return false;
        }
        
        return CommentLike::where('user_id', $userId)
            ->where('comment_id', $commentId)
            ->exists();
    }
} 
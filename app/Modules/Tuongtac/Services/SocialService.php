<?php

namespace App\Modules\Tuongtac\Services;

use App\Modules\Tuongtac\Models\TMotionItem;
use App\Modules\Tuongtac\Models\TComment;
use App\Modules\Tuongtac\Models\TNotice;
use App\Modules\Tuongtac\Models\TUserpage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service class for handling social interactions (likes, comments, shares)
 */
class SocialService
{
    /**
     * Danh sách các loại reaction có sẵn
     */
    protected $availableReactions = [
        'Like' => ['emoji' => '👍', 'color' => '#2078f4'],
        'Love' => ['emoji' => '❤️', 'color' => '#f33e58'],
        'Haha' => ['emoji' => '😆', 'color' => '#f7b125'],
        'Wow' => ['emoji' => '😮', 'color' => '#f7b125'],
        'Sad' => ['emoji' => '😢', 'color' => '#f7b125'],
        'Angry' => ['emoji' => '😠', 'color' => '#e9710f']
    ];

    /**
     * Get all social interactions for a piece of content
     *
     * @param int $itemId
     * @param string $itemCode
     * @return array
     */
    public function getInteractions($itemId, $itemCode)
    {
        $userId = Auth::id();
        
        // Get reaction data
        $reactions = $this->getReactions($itemId, $itemCode);
        
        // Get comment data
        $commentCount = $this->getCommentCount($itemId, $itemCode);
        
        // Combine data
        return [
            'item_id' => $itemId,
            'item_code' => $itemCode,
            'reactions' => $reactions,
            'userHasReacted' => $this->hasUserReacted($itemId, $itemCode, $userId),
            'userReactionType' => $this->getUserReactionType($itemId, $itemCode, $userId),
            'commentCount' => $commentCount,
            'shareCount' => $this->getShareCount($itemId, $itemCode),
            'availableReactions' => $this->availableReactions,
        ];
    }

    /**
     * Get HTML for social interactions section
     *
     * @param int $itemId
     * @param string $itemCode
     * @param string $slug Optional slug for sharing
     * @return string
     */
    public function getInteractionsHtml($itemId, $itemCode, $slug = '')
    {
        $data = $this->getInteractions($itemId, $itemCode);
        $data['slug'] = $slug;
        
        // Return HTML for social interactions
        return view('Tuongtac::frontend.actionbar.interactions', $data)->render();
    }

    /**
     * Get reaction data for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @return array
     */
    public function getReactions($itemId, $itemCode)
    {
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if (!$motionItem) {
            return [
                'total' => 0,
                'counts' => [],
            ];
        }
        
        return [
            'total' => $motionItem->getTotalReactionsCount(),
            'counts' => $motionItem->motions ?? [],
        ];
    }

    /**
     * Check if user has reacted to content
     *
     * @param int $itemId
     * @param string $itemCode
     * @param int|null $userId
     * @return bool
     */
    public function hasUserReacted($itemId, $itemCode, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return false;
        }
        
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if (!$motionItem || !$motionItem->user_motions) {
            return false;
        }
        
        foreach ($motionItem->user_motions as $type => $users) {
            if (in_array($userId, (array)$users)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get user's reaction type for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @param int|null $userId
     * @return string|null
     */
    public function getUserReactionType($itemId, $itemCode, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return null;
        }
        
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if (!$motionItem || !$motionItem->user_motions) {
            return null;
        }
        
        foreach ($motionItem->user_motions as $type => $users) {
            if (in_array($userId, (array)$users)) {
                return $type;
            }
        }
        
        return null;
    }

    /**
     * Toggle reaction for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @param string $reactionType
     * @param int|null $userId
     * @return array
     */
    public function toggleReaction($itemId, $itemCode, $reactionType, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            throw new \Exception('User not authenticated');
        }
        
        if (!array_key_exists($reactionType, $this->availableReactions)) {
            throw new \Exception('Invalid reaction type');
        }
        
        // Add points for engagemen
        
        // Get or create motion item
        $motionItem = TMotionItem::getOrCreate($itemId, $itemCode);
        
        // Toggle the reaction
        $added = $motionItem->toggleReaction($userId, $reactionType);
        
        // Get current reaction if any
        $currentReaction = null;
        foreach (($motionItem->user_motions ?? []) as $type => $users) {
            if (in_array($userId, (array)$users)) {
                $currentReaction = $type;
                break;
            }
        }
        
        return [
            'success' => true,
            'reactions' => $motionItem->motions,
            'status' => $added ? 'added' : 'removed',
            'userReactionType' => $currentReaction,
            'reactionDetails' => $currentReaction ? $this->availableReactions[$currentReaction] : null
        ];
    }

    /**
     * Get comment count for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @return int
     */
    public function getCommentCount($itemId, $itemCode)
    {
        return TComment::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get comments HTML for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @return string
     */
    public function getCommentsHtml($itemId, $itemCode)
    {
        $comments = DB::table('t_comments')
            ->select('t_comments.*', 'u.full_name', 'u.photo as photo')
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('status', 'active')
            ->where('parent_id', 0)
            ->leftJoin(DB::raw('(select id, full_name, photo from users) as u'), 't_comments.user_id', '=', 'u.id')
            ->orderBy('created_at', 'desc') // Sắp xếp bình luận mới nhất lên trên
            ->get();
        
        foreach ($comments as $comment) {
            $subcomments = DB::table('t_comments')
                ->select('t_comments.*', 'u.full_name', 'u.photo as photo')
                ->where('item_id', $itemId)
                ->where('item_code', $itemCode)
                ->where('status', 'active')
                ->where('parent_id', $comment->id)
                ->leftJoin(DB::raw('(select id, full_name, photo from users) as u'), 't_comments.user_id', '=', 'u.id')
                ->orderBy('created_at', 'asc')
                ->get();

            $comment->subcomments = $subcomments;
        }
        
        $data = [
            'item_id' => $itemId,
            'item_code' => $itemCode,
            'comments' => $comments,
            'curuser' => Auth::user()
        ];
       
        return view('Tuongtac::frontend.comments.show', $data)->render();
    }

    /**
     * Add comment to content
     *
     * @param int $itemId
     * @param string $itemCode
     * @param string $content
     * @param int $parentId
     * @param int|null $userId
     * @return array
     */
    public function addComment($itemId, $itemCode, $content, $parentId = 0, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'User not authenticated'
            ];
        }
        
        // Add points for engagement - đã được xử lý trong TCommentController
        // Nên loại bỏ để tránh lỗi và trùng lặp điểm thưởng
        
        $data = [
            'item_id' => $itemId,
            'item_code' => $itemCode,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId,
            'status' => 'active'
        ];
        
        try {
            // Save comment
            $comment = TComment::create($data);
            
            // Add notifications based on item type
            $this->createCommentNotification($comment, $data);
            
            // Get updated comment count
            $newCount = $this->getCommentCount($itemId, $itemCode);
            
            // Get HTML for comments
            $commentsHtml = $this->getCommentsHtml($itemId, $itemCode);
            
            return [
                'success' => true,
                'comment' => $comment,
                'newCount' => $newCount,
                'commentsHtml' => $commentsHtml
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create notification for comment
     *
     * @param TComment $comment
     * @param array $data
     * @return void
     */
    private function createCommentNotification($comment, $data)
    {
        // Different implementations based on item_code
        switch ($data['item_code']) {
            case 'tblog':
                $this->createTBlogCommentNotification($comment, $data);
                break;
            case 'book':
                // Implement for books if needed
                break;
            default:
                // Default implementation
                break;
        }
    }

    /**
     * Create notification for blog comment
     *
     * @param TComment $comment
     * @param array $data
     * @return void
     */
    private function createTBlogCommentNotification($comment, $data)
    {
        // Get the blog
        $blog = DB::table('t_blogs')->where('id', $data['item_id'])->first();
        
        if (!$blog || $blog->user_id == $data['user_id']) {
            return; // Don't notify yourself
        }
        
        // Kiểm tra xem có thông báo nào được tạo gần đây không (trong vòng 1 phút)
        $existingNotice = TNotice::where('item_id', $data['item_id'])
            ->where('item_code', $data['item_code'])
            ->where('user_id', $blog->user_id)
            ->where('created_at', '>=', now()->subMinutes(1))
            ->first();
        
        // Nếu đã có thông báo tương tự, không tạo nữa
        if ($existingNotice) {
            return;
        }
        
        $user = Auth::user();
        
        TNotice::create([
            'user_id' => $blog->user_id,
            'item_id' => $data['item_id'],
            'item_code' => $data['item_code'],
            'title' => $user->full_name . ' thêm bình luận bài viết',
            'url_view' => route('front.tblogs.show', $blog->slug)
        ]);
    }

    /**
     * Delete a comment
     * 
     * @param int $commentId
     * @param int $itemId
     * @param string $itemCode
     * @param int|null $userId
     * @return array
     */
    public function deleteComment($commentId, $itemId, $itemCode, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'User not authenticated'
            ];
        }
        
        $comment = TComment::find($commentId);
        
        if (!$comment) {
            return [
                'success' => false,
                'message' => 'Comment not found'
            ];
        }
        
        $user = Auth::user();
        
        // Check if user has permission to delete
        if ($comment->user_id != $userId && (!$user || $user->role != 'admin')) {
            return [
                'success' => false,
                'message' => 'Không có quyền xóa bình luận này'
            ];
        }
        
        try {
            // Delete replies
            TComment::where('parent_id', $commentId)->delete();
            
            // Delete comment
            $comment->delete();
            
            // Get updated comment count
            $newCount = $this->getCommentCount($itemId, $itemCode);
            
            // Get HTML for comments
            $commentsHtml = $this->getCommentsHtml($itemId, $itemCode);
            
            return [
                'success' => true,
                'newCount' => $newCount,
                'commentsHtml' => $commentsHtml
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Record a share for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @param int|null $userId
     * @return array
     */
    public function recordShare($itemId, $itemCode, $userId = null)
    {
        if ($userId === null) {
            $userId = Auth::id();
        }
        
        // Add points for sharing
        if ($userId) {
            TUserpage::add_points($userId, 2);
        }
        
        // Tìm hoặc tạo motion item
        $motionItem = TMotionItem::getOrCreate($itemId, $itemCode);
        
        // Tăng số lượt chia sẻ
        if (!isset($motionItem->shares)) {
            $motionItem->shares = 1;
        } else {
            $motionItem->shares = (int)$motionItem->shares + 1;
        }
        
        // Lưu lại
        $motionItem->save();
        
        return [
            'success' => true,
            'message' => 'Share recorded',
            'count' => $motionItem->shares
        ];
    }

    /**
     * Get share count for content
     *
     * @param int $itemId
     * @param string $itemCode
     * @return int
     */
    public function getShareCount($itemId, $itemCode)
    {
        // Kiểm tra từ bảng t_motion_items xem có trường shares không
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if ($motionItem && isset($motionItem->shares)) {
            return (int)$motionItem->shares;
        }
        
        // Nếu không có bản ghi hoặc không có trường shares, trả về 0
        return 0;
    }
} 
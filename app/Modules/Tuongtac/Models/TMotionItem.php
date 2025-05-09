<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class TMotionItem extends Model
{
    use HasFactory;
    protected $table = 't_motion_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'item_id',
        'item_code',
        'motions', 
        'user_motions',
    ];
    
    protected $casts = [
        'motions' => 'array', // Chuyển đổi thành mảng tự động
        'user_motions'=> 'array',
    ];
   
    public function blog()
    {
        return $this->belongsTo(TBlog::class, 'item_id')->where('item_code', 'tblog');
    }

    /**
     * Check if a user has reacted with a specific reaction
     *
     * @param int $userId User ID
     * @param string $reactionType Reaction type (Like, Love, etc)
     * @return bool
     */
    public function hasUserReacted($userId, $reactionType = 'Like')
    {
        if (!$this->user_motions || !isset($this->user_motions[$reactionType])) {
            return false;
        }
        
        return in_array($userId, $this->user_motions[$reactionType]);
    }

    /**
     * Get total count of a specific reaction
     *
     * @param string $reactionType Reaction type (Like, Love, etc)
     * @return int
     */
    public function getReactionCount($reactionType = 'Like')
    {
        if (!$this->motions || !isset($this->motions[$reactionType])) {
            return 0;
        }
        
        return (int)$this->motions[$reactionType];
    }

    /**
     * Get total reactions count (all types)
     *
     * @return int
     */
    public function getTotalReactionsCount()
    {
        if (!$this->motions) {
            return 0;
        }
        
        return array_sum($this->motions);
    }

    /**
     * Toggle a user's reaction
     *
     * @param int $userId User ID
     * @param string $reactionType Reaction type (Like, Love, etc)
     * @return bool True if reaction was added, false if removed
     */
    public function toggleReaction($userId, $reactionType = 'Like')
    {
        // Initialize arrays if they're empty
        $motions = $this->motions ?? [];
        $userMotions = $this->user_motions ?? [];
        
        // Initialize the reaction type if it doesn't exist
        if (!isset($motions[$reactionType])) {
            $motions[$reactionType] = 0;
        }
        
        if (!isset($userMotions[$reactionType])) {
            $userMotions[$reactionType] = [];
        }
        
        // Check if user has already reacted
        $hasReacted = in_array($userId, $userMotions[$reactionType]);
        
        if ($hasReacted) {
            // Remove reaction
            $userMotions[$reactionType] = array_diff($userMotions[$reactionType], [$userId]);
            $motions[$reactionType] = max(0, $motions[$reactionType] - 1);
            $added = false;
        } else {
            // Add reaction
            $userMotions[$reactionType][] = $userId;
            $motions[$reactionType]++;
            $added = true;
        }
        
        // Update the model
        $this->motions = $motions;
        $this->user_motions = $userMotions;
        $this->save();
        
        return $added;
    }

    /**
     * Get a motion item for a specific content, or create if it doesn't exist
     *
     * @param int $itemId Content ID
     * @param string $itemCode Content type code
     * @return TMotionItem
     */
    public static function getOrCreate($itemId, $itemCode)
    {
        $motionItem = self::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if (!$motionItem) {
            $motionItem = self::create([
                'item_id' => $itemId,
                'item_code' => $itemCode,
                'motions' => [],
                'user_motions' => []
            ]);
        }
        
        return $motionItem;
    }
}
 
 
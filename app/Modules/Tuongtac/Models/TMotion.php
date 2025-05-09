<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Tuongtac\Models\TMotionItem;


class TMotion extends Model
{
    use HasFactory;
    protected $table = 't_motions'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'icon',
        'status', 
    ];
   
    /**
     * Check if a user has reacted to an item
     *
     * @param int $itemId Content ID
     * @param string $itemCode Content type code
     * @param int $userId User ID
     * @param string $reactionType Reaction type (Like, Love, etc)
     * @return bool
     */
    public static function checkUserReacted($itemId, $itemCode, $userId, $reactionType = 'Like')
    {
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        if (!$motionItem) {
            return false;
        }
        
        return $motionItem->hasUserReacted($userId, $reactionType);
    }
}
 
 
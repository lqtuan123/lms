<?php

namespace App\Modules\Tuongtac\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Tuongtac\Models\TMotionItem;
use Illuminate\Support\Facades\Auth;

class TMotionController extends Controller
{
    /**
     * Toggle like cho bài viết
     */
    public function toggleLike(Request $request)
    {
        if(!Auth::check())
        {
            return response()->json(['success' => false, 'msg' => 'Bạn phải đăng nhập']);
        }
        
        $request->validate([
            'item_id' => 'required|integer', 
            'item_code'=> 'required|string'
        ]);
        
        $itemId = $request->item_id;
        $itemCode = $request->item_code;
        
        // Get or create the motion item
        $motionItem = TMotionItem::getOrCreate($itemId, $itemCode);
        
        // Toggle the reaction
        $added = $motionItem->toggleReaction(Auth::id(), 'Like');
        
        return response()->json([
            'success' => true,
            'status' => $added ? 'added' : 'removed',
            'count' => $motionItem->getReactionCount('Like')
        ]);
    }
} 
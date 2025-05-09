<?php

namespace App\Modules\Tuongtac\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TTagItem;
use  App\Modules\Tuongtac\Models\TMotion;
use  App\Modules\Tuongtac\Models\TMotionItem;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TMotionItemController extends Controller
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
     * React to content (like, love, etc.)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function react(Request $request)
    {
        if(!Auth::check())
        {
            return response()->json(['success' => false, 'msg' => 'Bạn phải đăng nhập']);
        }
        
        $request->validate([
            'item_id' => 'required|integer', 
            'item_code'=> 'required|string', 
            'reaction_id' => 'required|string', // Ex: 'Like', 'Love', etc.
        ]);
        
        if (!array_key_exists($request->reaction_id, $this->availableReactions)) {
            return response()->json(['success' => false, 'msg' => 'Loại tương tác không hợp lệ']);
        }
        
        // Get or create the motion item
        $motionItem = TMotionItem::getOrCreate($request->item_id, $request->item_code);
        
        // Kiểm tra xem người dùng đã reaction trước đó chưa
        $hasReacted = false;
        foreach (($motionItem->user_motions ?? []) as $type => $users) {
            if (in_array(Auth::id(), (array)$users)) {
                $hasReacted = true;
                break;
            }
        }
        
        // Toggle the reaction
        $added = $motionItem->toggleReaction(Auth::id(), $request->reaction_id);
        
        // Nếu đây là lần đầu thả reaction, tính điểm thưởng
        if ($added && !$hasReacted) {
            try {
                Auth::user()->addPoint('like_post', $request->item_id, $request->item_code);
            } catch (\Exception $e) {
                // Xử lý lỗi nếu có, không làm gián đoạn quy trình
                \Log::error('Lỗi khi tính điểm reaction: ' . $e->getMessage());
            }
        }
        
        // Nếu đã thêm reaction, gửi thông báo cho chủ bài viết
        if ($added) {
            $this->sendReactionNotification(
                $request->item_id,
                $request->item_code,
                $request->reaction_id,
                Auth::id()
            );
        }
        
        // Get user's current reaction type
        $userReaction = null;
        foreach (($motionItem->user_motions ?? []) as $type => $users) {
            if (in_array(Auth::id(), (array)$users)) {
                $userReaction = $type;
                break;
            }
        }
        
        return response()->json([
            'success' => true, 
            'reactions' => $motionItem->motions,
            'status' => $added ? 'added' : 'removed',
            'userReaction' => $userReaction,
            'reactionDetails' => $userReaction ? $this->availableReactions[$userReaction] : null
        ]);
    }
    
    /**
     * Gửi thông báo khi có người thích/reaction bài viết
     */
    private function sendReactionNotification($itemId, $itemCode, $reactionType, $userId)
    {
        // Xác định chủ sở hữu của nội dung
        $ownerId = null;
        
        if ($itemCode == 'tblog') {
            $blog = \App\Modules\Tuongtac\Models\TBlog::find($itemId);
            if ($blog) {
                $ownerId = $blog->user_id;
            }
        }
        
        // Nếu tìm thấy chủ sở hữu và không phải là người dùng hiện tại, gửi thông báo
        if ($ownerId && $ownerId != $userId) {
            // Kiểm tra xem đã có thông báo reaction nào được tạo gần đây không (trong vòng 1 phút)
            $existingNotice = \App\Modules\Tuongtac\Models\TNotice::where('item_id', $itemId)
                ->where('item_code', $itemCode)
                ->where('user_id', $ownerId)
                ->where('created_at', '>=', now()->subMinutes(1))
                ->first();
            
            // Nếu đã có thông báo gần đây, không tạo thêm nữa
            if ($existingNotice) {
                return;
            }
            
            $noticeController = new \App\Modules\Tuongtac\Controllers\TNoticeController();
            $noticeController->notifyReaction(
                $itemId,
                $itemCode,
                $ownerId,
                $userId,
                $reactionType
            );
        }
    }
    
    /**
     * Get reaction status for a piece of content
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReactionStatus(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer', 
            'item_code'=> 'required|string'
        ]);
        
        $motionItem = TMotionItem::where('item_id', $request->item_id)
            ->where('item_code', $request->item_code)
            ->first();
            
        $data = [
            'total_reactions' => 0,
            'user_has_reacted' => false,
            'user_reaction_type' => null,
            'reaction_counts' => [],
            'available_reactions' => $this->availableReactions
        ];
        
        if ($motionItem) {
            $data['total_reactions'] = $motionItem->getTotalReactionsCount();
            $data['reaction_counts'] = $motionItem->motions;
            
            if (Auth::check()) {
                // Check if the current user has reacted
                foreach (($motionItem->user_motions ?? []) as $type => $users) {
                    if (in_array(Auth::id(), (array)$users)) {
                        $data['user_has_reacted'] = true;
                        $data['user_reaction_type'] = $type;
                        if (isset($this->availableReactions[$type])) {
                            $data['reaction_details'] = $this->availableReactions[$type];
                        }
                        break;
                    }
                }
            }
        }
        
        return response()->json($data);
    }
    
    /**
     * Get HTML for reaction icons and counts
     * 
     * @param int $itemId
     * @param string $itemCode
     * @return string HTML
     */
    public static function getReactionHtml($itemId, $itemCode)
    {
        $motionItem = TMotionItem::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
            
        $hasReacted = false;
        $reactionType = null;
        $totalReactions = 0;
        
        if ($motionItem) {
            $totalReactions = $motionItem->getTotalReactionsCount();
            
            if (Auth::check()) {
                foreach (($motionItem->user_motions ?? []) as $type => $users) {
                    if (in_array(Auth::id(), (array)$users)) {
                        $hasReacted = true;
                        $reactionType = $type;
                        break;
                    }
                }
            }
        }
        
        // Determine emoji based on reaction type
        $emoji = '👍';
        $color = '#2078f4';
        $text = 'Like';
        
        switch ($reactionType) {
            case 'Love':
                $emoji = '❤️';
                $color = '#f33e58';
                $text = 'Love';
                break;
            case 'Haha':
                $emoji = '😆';
                $color = '#f7b125';
                $text = 'Haha';
                break;
            case 'Wow':
                $emoji = '😮';
                $color = '#f7b125';
                $text = 'Wow';
                break;
            case 'Sad':
                $emoji = '😢';
                $color = '#f7b125';
                $text = 'Sad';
                break;
            case 'Angry':
                $emoji = '😠';
                $color = '#e9710f';
                $text = 'Angry';
                break;
        }
        
        $style = $hasReacted ? 'color: ' . $color . ';' : '';
        
        $html = '<div class="flex items-center">';
        if ($hasReacted) {
            $html .= '<span style="font-size:16px; margin-right:5px;">' . $emoji . '</span>';
        } else {
            $html .= '<i class="far fa-thumbs-up mr-1"></i>';
        }
        $html .= '<span class="text-xs">' . $totalReactions . '</span>';
        $html .= '</div>';
        
        return $html;
    }
}
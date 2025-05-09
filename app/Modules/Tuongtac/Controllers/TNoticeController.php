<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TNoticeController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
    }
    
    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead($id)
    {
        try
        {
            $notification = TNotice::find($id);
            $user = Auth::user();
            if ($notification && $notification->user_id == $user->id) {
                $notification->seen = 0; // 0 = đã đọc
                $notification->save();
                
                return response()->json(['success' => true]);
            }
        }
        catch (\Exception $e) {

            return response()->json(['success' => false,'msg'=>'lỗi'. $e]);
        }
    }
    
    /**
     * Tạo thông báo mới
     */
    public function createNotification($userId, $itemId, $itemCode, $title, $url, $userFromId = null)
    {
        try {
            $notice = new TNotice();
            $notice->user_id = $userId;
            $notice->item_id = $itemId;
            $notice->item_code = $itemCode;
            $notice->title = $title; 
            $notice->url_view = $url;
            $notice->seen = 1; // 1 = chưa đọc
            
            // Lưu ID người gửi thông báo nếu có
            if ($userFromId) {
                $notice->user_from_id = $userFromId;
            }
            
            $notice->save();
            
            return $notice;
        } catch (\Exception $e) {
            Log::error('Lỗi tạo thông báo: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Tạo thông báo cho comment
     */
    public function notifyComment($commentId, $itemId, $itemCode, $authorId, $commenterId)
    {
        // Không tạo thông báo khi người dùng tự comment bài viết của mình
        if ($authorId == $commenterId) {
            return null;
        }
        
        $itemType = '';
        $url = '';
        
        if ($itemCode == 'tblog') {
            $blog = \App\Modules\Tuongtac\Models\TBlog::find($itemId);
            if ($blog) {
                $itemType = 'bài viết';
                $url = route('front.tblogs.show', $blog->slug);
            }
        }
        
        if (empty($url)) {
            return null;
        }
        
        $commenter = User::find($commenterId);
        $commenterName = $commenter ? $commenter->name : 'Ai đó';
        
        $title = "{$commenterName} đã bình luận về {$itemType} của bạn";
        
        return $this->createNotification($authorId, $itemId, $itemCode, $title, $url, $commenterId);
    }
    
    /**
     * Tạo thông báo cho like/reaction
     */
    public function notifyReaction($itemId, $itemCode, $authorId, $reactorId, $reactionType)
    {
        // Không tạo thông báo khi người dùng tự like bài viết của mình
        if ($authorId == $reactorId) {
            return null;
        }
        
        $itemType = '';
        $url = '';
        
        if ($itemCode == 'tblog') {
            $blog = \App\Modules\Tuongtac\Models\TBlog::find($itemId);
            if ($blog) {
                $itemType = 'bài viết';
                $url = route('front.tblogs.show', $blog->slug);
            }
        }
        
        if (empty($url)) {
            return null;
        }
        
        $reactor = User::find($reactorId);
        $reactorName = $reactor ? $reactor->name : 'Ai đó';
        
        $reactionText = 'thích';
        switch ($reactionType) {
            case 'Love':
                $reactionText = 'yêu thích';
                break;
            case 'Haha':
                $reactionText = 'cười về';
                break;
            case 'Wow':
                $reactionText = 'ngạc nhiên về';
                break;
            case 'Sad':
                $reactionText = 'buồn về';
                break;
            case 'Angry':
                $reactionText = 'giận dữ về';
                break;
        }
        
        $title = "{$reactorName} đã {$reactionText} {$itemType} của bạn";
        
        return $this->createNotification($authorId, $itemId, $itemCode, $title, $url, $reactorId);
    }
    
    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            TNotice::where('user_id', $user->id)
                ->where('seen', 1)
                ->update(['seen' => 0]);
                
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Đếm số thông báo chưa đọc
     */
    public function countUnread()
    {
        $user = Auth::user();
        $count = TNotice::where('user_id', $user->id)
            ->where('seen', 1)
            ->count();
            
        return response()->json(['count' => $count]);
    }
    
    /**
     * Lấy danh sách thông báo chưa đọc
     */
    public function getNotice(Request $request)
    {
        $user = Auth::user();
        $filter = $request->input('filter', 'all');
        $limit = $request->input('limit', $this->pagesize);
        
        $query = TNotice::where('user_id', $user->id)
            ->with('userFrom') // Load thông tin người gửi thông báo
            ->orderBy('id', 'desc');
            
        // Lọc theo trạng thái đọc/chưa đọc
        if ($filter === 'unread') {
            $query->where('seen', 1); // Chỉ lấy thông báo chưa đọc
        }
        
        $data['notices'] = $query->paginate($limit);
        return view('Tuongtac::frontend.notices.notices', $data)->render();
    }
    
    /**
     * Hiển thị trang danh sách tất cả thông báo
     */
    public function index()
    {
        $user = Auth::user();
        $data['notifications'] = TNotice::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($this->pagesize);
            
        $data['pagetitle'] = "Thông báo của bạn";
        $data['page_up_title'] = "Thông báo của bạn";
        
        return view('frontend.profile.notifications', $data);
    }
}
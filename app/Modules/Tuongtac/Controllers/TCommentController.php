<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tuongtac\Models\TComment;
use App\Modules\Tuongtac\Models\TNotice;
use App\Modules\Tuongtac\Models\TBlog;
use App\Modules\Tuongtac\Models\TUserpage;
use App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Modules\Tuongtac\Services\SocialService;

class TCommentController extends Controller
{
    protected $socialService;
    
    public function __construct(SocialService $socialService)
    {
        $this->socialService = $socialService;
    }
    
    public function index()
    {
        return view('Tuongtac::frontend.index');
    }

    /**
     * Save a new comment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveComment(Request $request)
    {
        $this->validate($request, [
            'content' => 'string|required',
            'item_id' => 'numeric|required',
            'item_code' => 'string|required',
            'parent_id' => 'numeric|required',
        ]);
        
        if (!Auth::check()) {
            return response()->json(['msg' => 'chưa đăng nhập', 'status' => false]);
        }
        
        $user = Auth::user();
        
        // Kiểm tra xem có comment trùng lặp gần đây không (trong vòng 10 giây)
        $recentComment = TComment::where('user_id', $user->id)
            ->where('item_id', $request->item_id)
            ->where('item_code', $request->item_code)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();
            
        if ($recentComment) {
            return response()->json([
                'status' => true,
                'msg' => $recentComment,
                'newCount' => TComment::where('item_id', $request->item_id)
                    ->where('item_code', $request->item_code)
                    ->where('status', 'active')
                    ->count(),
                'commentsHtml' => $this->socialService->getCommentsHtml($request->item_id, $request->item_code)
            ]);
        }
        
        // Xử lý thông qua service
        $result = $this->socialService->addComment(
            $request->item_id,
            $request->item_code,
            $request->content,
            $request->parent_id,
            $user->id
        );
        
        if ($result['success']) {
            $comment = $result['comment'];
            $comment->full_name = $user->full_name;
            $comment->photo = $user->photo;
            
            // Tính điểm cho người dùng - đảm bảo chỉ tính 1 lần
            if (Auth::check()) {
                Auth::user()->addPoint('create_comment', $comment->item_id, $comment->item_code);
            }
            
            // Tạo thông báo cho người viết bài
            $this->sendCommentNotification($comment);
            
            return response()->json([
                'status' => true,
                'msg' => $comment,
                'newCount' => $result['newCount'],
                'commentsHtml' => $result['commentsHtml']
            ]);
        } else {
            return response()->json(['msg' => $result['message'], 'status' => false]);
        }
    }

    /**
     * Process comment content to preserve book tags
     * 
     * @param string $content
     * @return string
     */
    private function processContent($content)
    {
        // No need to change anything, just return
        // Book tag format is already processed on client-side: [Book Title](#bookId)
        return $content;
    }

    /**
     * Update an existing comment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment(Request $request)
    {
        $this->validate($request, [
            'content' => 'string|required',
            'item_id' => 'numeric|required',
            'item_code' => 'string|required',
            'id' => 'numeric|required',
        ]);
        
        if (!Auth::check()) {
            return response()->json(['msg' => 'chưa đăng nhập', 'status' => false]);
        }
        
        $comment = TComment::find($request->id);
        
        if (!$comment) {
            return response()->json(['msg' => 'không tìm thấy dữ liệu', 'status' => false]);
        }
        
        if (Auth::id() != $comment->user_id) {
            return response()->json(['msg' => 'bạn không phải là tác giả', 'status' => false]);
        }
        
        $data = $request->only(['content', 'item_id', 'item_code']);
        $comment->fill($data)->save();
        
        // Lấy comments HTML mới
        $commentsHtml = $this->socialService->getCommentsHtml($request->item_id, $request->item_code);
        
        return response()->json([
            'status' => true, 
            'msg' => $comment,
            'commentsHtml' => $commentsHtml
        ]);
    }

    /**
     * Show comments of an item
     */
    public function show(Request $request, $itemId = null, $itemCode = null)
    {
        // Sử dụng hoặc từ route hoặc từ params
        $item_id = $itemId ?? $request->input('item_id', 0);
        $item_code = $itemCode ?? $request->input('item_code', 'other');
        
        if (!$item_id || !$item_code) {
            return $this->error('Thiếu thông tin item ID hoặc item code');
        }
        
        // Nếu là AJAX request, trả về HTML
        if ($request->ajax()) {
            return $this->socialService->getCommentsHtml($item_id, $item_code);
        }
        
        // Get comments for this item
        $comments = $this->getItemComments($item_id, $item_code);
        
        // Pass current user if logged in
        $curuser = Auth::user();
        
        // Return view with comments data
        return view('Tuongtac::frontend.comments.show', [
            'comments' => $comments,
            'item_id' => $item_id,
            'item_code' => $item_code,
            'curuser' => $curuser
        ]);
    }

    /**
     * Get comments for an item with pagination
     * 
     * @param int $itemId
     * @param string $itemCode
     * @param int $page
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getItemComments($itemId, $itemCode, $page = 1, $limit = 10)
    {
        $comments = TComment::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('parent_id', 0)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get user information for each comment
        foreach ($comments as $comment) {
            $user = User::find($comment->user_id);
            if ($user) {
                $comment->full_name = $user->full_name;
                $comment->photo = $user->photo ?: asset('assets/images/placeholder.jpg');
            } else {
                $comment->full_name = 'Unknown User';
                $comment->photo = asset('assets/images/placeholder.jpg');
            }
            
            // Get subcomments (replies)
            $comment->subcomments = $this->getCommentReplies($comment->id);
        }
        
        return $comments;
    }

    /**
     * Get replies for a comment
     * 
     * @param int $commentId
     * @return \Illuminate\Support\Collection
     */
    private function getCommentReplies($commentId)
    {
        $replies = TComment::where('parent_id', $commentId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Get user information for each reply
        foreach ($replies as $reply) {
            $user = User::find($reply->user_id);
            if ($user) {
                $reply->full_name = $user->full_name;
                $reply->photo = $user->photo ?: asset('assets/images/placeholder.jpg');
            } else {
                $reply->full_name = 'Unknown User';
                $reply->photo = asset('assets/images/placeholder.jpg');
            }
        }
        
        return $replies;
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'msg' => 'Bạn cần đăng nhập để thực hiện chức năng này.'
            ]);
        }
        
        $comment_id = $request->input('id');
        $item_id = $request->input('item_id');
        $item_code = $request->input('item_code');
        
        $result = $this->socialService->deleteComment(
            $comment_id,
            $item_id,
            $item_code,
            Auth::id()
        );
        
        if ($result['success']) {
            return response()->json([
                'status' => true,
                'msg' => 'Xóa bình luận thành công.',
                'newCount' => $result['newCount'],
                'commentsHtml' => $result['commentsHtml']
            ]);
        } else {
            return response()->json([
                'status' => false,
                'msg' => $result['message']
            ]);
        }
    }

    /**
     * Get comments for an item
     *
     * @param int $itemId
     * @param string $itemCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments($itemId, $itemCode)
    {
        $comments = TComment::with('user')
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('status', 'active')
            ->where('parent_id', 0)
            ->orderBy('created_at', 'desc')
            ->get();
            
        foreach ($comments as $comment) {
            $comment->replies = TComment::with('user')
                ->where('parent_id', $comment->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'asc')
                ->get();
        }
        
        return response()->json([
            'comments' => $comments,
            'total' => $comments->count(),
            'html' => $this->socialService->getCommentsHtml($itemId, $itemCode)
        ]);
    }

    /**
     * Get active comments HTML for a content item
     *
     * @param int $itemId
     * @param string $itemCode
     * @return string
     */
    public static function getCommentActive($itemId, $itemCode)
    {
        $comments = DB::table('t_comments')
            ->select('t_comments.*', 'u.full_name', 'u.photo as photo')
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('status', 'active')
            ->where('parent_id', 0)
            ->leftJoin(DB::raw('(select id, full_name, photo from users) as u'), 't_comments.user_id', '=', 'u.id')
            ->orderBy('created_at', 'desc')
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
            'curuser' => Auth::user() // Only set if authenticated
        ];
       
        return view('Tuongtac::frontend.comments.show', $data)->render();
    }
    
    /**
     * Get comment count for an item
     *
     * @param int $itemId
     * @param string $itemCode
     * @return int
     */
    public static function getCommentCount($itemId, $itemCode)
    {
        return TComment::where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->where('status', 'active')
            ->count();
    }
    
    /**
     * Save a comment from array data
     *
     * @param array $data
     * @return TComment
     */
    public function saveCommentArr($data)
    {
        $comment = TComment::create($data);
        
        // Create notification based on item type
        if ($data['item_code'] == 'tblog') {
            $this->createBlogCommentNotification($comment, $data);
        } elseif ($data['item_code'] == 'blog') {
            $this->createBlogCommentNotification($comment, $data, \App\Models\Blog::class);
        } elseif ($data['item_code'] == 'congviec') {
            $this->createJobCommentNotification($comment, $data);
        } elseif ($data['item_code'] == 'ads') {
            $this->createAdsCommentNotification($comment, $data);
        }
        
        return $comment;
    }
    
    /**
     * Create notification for blog comment
     *
     * @param TComment $comment
     * @param array $data
     * @param string $modelClass
     */
    private function createBlogCommentNotification($comment, $data, $modelClass = null)
    {
        if ($modelClass === null) {
            $modelClass = TBlog::class;
        }
        
        // Check if class exists before using it
        if (!class_exists($modelClass)) {
            return;
        }
        
        $blog = $modelClass::findOrFail($data['item_id']);
        $user = User::find($data['user_id']);
        
        // Don't notify yourself
        if ($blog->user_id == $data['user_id']) {
            return;
        }
        
        $notice = [
            'user_id' => $blog->user_id,
            'item_id' => $data['item_id'],
            'item_code' => $data['item_code'],
            'title' => $user->full_name . ' thêm bình luận bài viết'
        ];
        
        // Set URL based on model class
        if ($modelClass == TBlog::class) {
            $notice['url_view'] = route('front.tblogs.show', $blog->slug);
        } else {
            $notice['url_view'] = route('front.page.view', $blog->slug);
        }
        
        TNotice::create($notice);
    }
    
    /**
     * Create notification for job comment
     *
     * @param TComment $comment
     * @param array $data
     */
    private function createJobCommentNotification($comment, $data)
    {
        // Check if JCongviec class exists
        if (!class_exists('App\Modules\Nguoitimviec\Models\JCongviec')) {
            return;
        }
        
        $congviec = JCongviec::findOrFail($data['item_id']);
        $user = User::find($data['user_id']);
        
        // Don't notify yourself
        if ($congviec->user_id == $data['user_id']) {
            return;
        }
        
        TNotice::create([
            'user_id' => $congviec->user_id,
            'item_id' => $data['item_id'],
            'item_code' => $data['item_code'],
            'title' => $user->full_name . ' thêm bình luận việc làm',
            'url_view' => route('front.vieclam.chitietvieclam', $congviec->id)
        ]);
    }
    
    /**
     * Create notification for ads comment
     *
     * @param TComment $comment
     * @param array $data
     */
    private function createAdsCommentNotification($comment, $data)
    {
        // Check if Ads class exists
        if (!class_exists('App\Models\Ads')) {
            return;
        }
        
        $ad = \App\Models\Ads::find($data['item_id']);
        
        if (!$ad) {
            return;
        }
        
        // Increase position
        $ad->position += 1;
        $ad->save();
        
        $user = User::find($data['user_id']);
        
        // Don't notify yourself
        if ($ad->user_id == $data['user_id']) {
            return;
        }
        
        TNotice::create([
            'user_id' => $ad->user_id,
            'item_id' => $data['item_id'],
            'item_code' => $data['item_code'],
            'title' => $user->full_name . ' thêm bình luận cắt lỗ xả hàng',
            'url_view' => route('front.ad.view', $ad->slug)
        ]);
    }
    
    /**
     * Toggle comment status (active/inactive)
     *
     * @param int $id
     * @return void
     */
    public function statusChange($id)
    {
        $comment = TComment::findOrFail($id);
        $comment->statusChange();
    }

    /**
     * Gửi thông báo khi có bình luận mới
     */
    private function sendCommentNotification($comment)
    {
        // Kiểm tra xem đã có thông báo nào được tạo cho comment này hay chưa
        $existingNotice = TNotice::where('item_id', $comment->item_id)
            ->where('item_code', $comment->item_code)
            ->where('created_at', '>=', now()->subMinutes(1)) // Chỉ kiểm tra thông báo được tạo trong vòng 1 phút
            ->first();
            
        // Nếu đã có thông báo gần đây, không tạo thêm nữa
        if ($existingNotice) {
            return;
        }
        
        $noticeController = new TNoticeController();
        
        // Xác định chủ sở hữu của nội dung được bình luận
        $ownerId = null;
        
        if ($comment->item_code == 'tblog') {
            $blog = TBlog::find($comment->item_id);
            if ($blog) {
                $ownerId = $blog->user_id;
            }
        }
        
        // Nếu tìm thấy chủ sở hữu, gửi thông báo
        if ($ownerId) {
            $noticeController->notifyComment(
                $comment->id,
                $comment->item_id,
                $comment->item_code,
                $ownerId,
                $comment->user_id
            );
        }
    }
}
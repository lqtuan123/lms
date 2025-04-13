<?php

namespace App\Modules\Tuongtac\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TTagItem;
use  App\Modules\Tuongtac\Models\TMotion;
use  App\Modules\Tuongtac\Models\TMotionItem;
use  App\Modules\Tuongtac\Models\TRecommend;
use  App\Modules\Tuongtac\Models\TUserpage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TRecommendController extends Controller
{
    public function toggleBookmark(Request $request )
    {
        if(!auth()->id())
        {
            return response()->json(['success' => false, 'msg' => 'Bạn phải đăng nhập']);
        }
        $request->validate([
            'item_id' => 'required|integer', 
            'item_code'=> 'required|string', 
           
        ]);
        $data= $request->all();
        $userId = auth()->id(); // Lấy ID của người dùng hiện tại
        $itemCode = $data['item_code']; // Loại mục (ví dụ: blog)
        $postId = $data['item_id'];

       
        
        // Kiểm tra xem bài viết đã được bookmark chưa
        $bookmarkExists = \DB::table('t_recommends')
            ->where('user_id', $userId)
            ->where('item_id', $postId)
            ->where('item_code', $itemCode)
            ->exists();
    
        if ($bookmarkExists) {
            // Nếu đã bookmark, xóa bookmark
            \DB::table('t_recommends')
                ->where('user_id', $userId)
                ->where('item_id', $postId)
                ->where('item_code', $itemCode)
                ->delete();
    
            $status = 'removed';
        } else {
            // Nếu chưa bookmark, thêm bookmark
            \DB::table('t_recommends')->insert([
                'user_id' => $userId,
                'item_id' => $postId,
                'item_code' => $itemCode,
                'created_at' => now(), // Nếu cần thêm thời gian
                'updated_at' => now(), // Nếu cần thêm thời gian
            ]);
    
            $status = 'added';
        }
    
        return response()->json(['status' => $status]);
    }

}
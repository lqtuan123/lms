<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TTagItem;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TMotion;
use  App\Modules\Tuongtac\Models\TMotionItem;
use  App\Modules\Tuongtac\Models\TRecommend;
use  App\Modules\Tuongtac\Models\TVoteItem;
use Illuminate\Support\Str;
class TVoteController extends Controller
{
    public function vote(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string', // Mã loại mục (ví dụ: 'blog', 'post')
            'point' => 'required|integer|min:1|max:5', // Điểm (1-5)
        ]);
    
        $userId = \Auth::id();
        if(!$userId)
            return response()->json(['success' => false, 'msg' =>  'Bạn cần đăng nhập!']);
        $itemCode = $request->input('item_code');
        $point = $request->input('point');
        $itemId = $request->item_id;
        $itemCode = $request->item_code;
        // Tìm bản ghi vote
        $voteRecord = DB::table('t_vote_items')
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->first();
       
            ///
        TUserpage::add_points(auth()->id(),1);

        if (!$voteRecord) {
            // Nếu chưa có bản ghi, tạo mới
            $votes = [$userId => $point];
            $data = [
                'item_id' => $itemId,
                'item_code' => $itemCode,
                'count' => 1,
                'point' => $point,
                'votes' => json_encode($votes),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $voteRecord = TVoteItem::Create($data);
        } 
        
        // Nếu đã có bản ghi, cập nhật
        $votes = json_decode($voteRecord->votes, true);

        // Kiểm tra nếu user đã vote
        if (isset($votes[$userId])) {
            // Cập nhật số điểm của user
            $votes[$userId] = $point;
        } else {
            // Thêm user mới vào
            $votes[$userId] = $point;
        }

        // Cập nhật `count` và `point`
        $count = count($votes);
        $totalPoints = array_sum($votes);
        $averagePoint = $totalPoints / $count;

        DB::table('t_vote_items')
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->update([
                'count' => $count,
                'point' => $averagePoint,
                'votes' => json_encode($votes),
                'updated_at' => now(),
            ]);
        
    
        return response()->json(['success' => true, 'averagePoint' =>  $averagePoint,'count'=>$count]);
    }
}
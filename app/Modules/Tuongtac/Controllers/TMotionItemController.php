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

class TMotionItemController extends Controller
{
   

    public function react(Request $request)
    {
        if(!auth()->id())
        {
            return response()->json(['success' => false, 'msg' => 'Bạn phải đăng nhập']);
        }
        $request->validate([
            'item_id' => 'required|integer', 
            'item_code'=> 'required|string', 
            'reaction_id' => 'required|string', // Ví dụ: 'like', 'love', ...
        ]);
        $validReactions = TMotion::pluck('title')->toArray();
        ///
        TUserpage::add_points(auth()->id(),1);
        // Lưu lại danh sách reactions
        $motionitem = TMotionItem::where('item_id', $request->item_id)->where('item_code',$request->item_code)->first();
        if(!$motionitem)
        {
            $data['item_id'] = $request->item_id;
            $data['item_code'] = $request->item_code;
            $motionitem = TMotionItem::create($data);
           
        }

        $reactionType = $request->input('reaction_id');
        $reactions = $motionitem->motions ?? array_fill_keys($validReactions, 0);
        // Tăng số lượng reaction tương ứng
        if (isset($reactions[$reactionType])) {
            $reactions[$reactionType]++;
        }
       
        $motionitem->motions = $reactions;
        
        $userId = auth()->id(); // ID người dùng hiện tại
      
        $userreactions = $motionitem->user_motions??  array_fill_keys($validReactions, []);
        foreach ($userreactions as $key => $userIds) {
            $userreactions[$key] = array_filter($userIds, fn($id) => $id != $userId);
        }

         // Thêm ID người dùng vào reaction mới
 
        if (isset($userreactions[$reactionType])) {
            $userreactions[$reactionType][] = $userId;
        }
        
        foreach ($userreactions as $key => $values) {
            $reactions[$key] = count(array_filter($values, function ($value) {
                return !is_null($value); // Bỏ qua các giá trị null
            }));
        }
        $motionitem->user_motions = $userreactions;
        $motionitem->motions = $reactions;
        $motionitem->save();

        return response()->json(['success' => true, 'reactions' => $motionitem->motions ]);
    }

}
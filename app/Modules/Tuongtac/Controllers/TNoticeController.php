<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TNoticeController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
    }
    public function markAsRead($id)
    {
        try
        {
            $notification = TNotice::find($id);
            $user = auth()->user();
            if ($notification && $notification->user_id == $user->id) {
                $notification->seen = 0; // giả sử bạn có trường `is_read` trong bảng notifications
                $notification->save();
                
                return response()->json(['success' => true]);
            }
        }
        catch (\Exception $e) {

            return response()->json(['success' => false,'msg'=>'lỗi'. $e]);
        }
    }
    public function getNotice(){
        $user = auth()->user();
        $data['notices'] = TNotice::where('user_id',$user->id)->where('seen',1)->orderBy('id','desc')->paginate($this->pagesize);
        return view('Tuongtac::frontend.notices.show',$data)->render();
    }
}
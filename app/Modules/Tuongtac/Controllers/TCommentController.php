<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TCommentController extends Controller
{
    public function index()
    {
        return view('Tuongtac::frontend.index');
    }
    /**
     * 'item_id',
     *   'item_code',
      *  'user_id', 
       * 'content', 
        *'resources',
     *
        **/
    public function saveComment(Request $request)
    {
        
        $this->validate($request,[
            'content'=>'string|required',
            'item_id'=>'numeric|required',
            'item_code'=>'string|required',
            'parent_id'=>'numeric|required',
        ]);
        $data = $request->all();
        $user = auth()->user();
        if(!$user)
        {
            return response()->json(['msg'=>'chưa đăng nhập','status'=>false]);
        }
        $data['user_id'] = $user->id;
        $comment = $this->saveCommentArr($data);
        $comment->full_name = $user->full_name;
        $comment->photo = $user->photo;

        TUserpage::add_points(auth()->id(),1);


        return response()->json(['msg'=>$comment,'status'=>true]);
    }
    public function updateComment(Request $request)
    {
        
        $this->validate($request,[
            'content'=>'string|required',
            'item_id'=>'numeric|required',
            'item_code'=>'string|required',
            'id'=>'numeric|required',
        ]);
        $data = $request->all();
        $user = auth()->user();
        if(!$user)
        {
            return response()->json(['msg'=>'chưa đăng nhập','status'=>false]);
        }
        $comment = TComment::find($data['id']);
        if(!$comment)
        {
            return response()->json(['msg'=>'không tìm thấy dữ liệu','status'=>false]);
        }
        if($user->id != $comment->user_id)
        {
            return response()->json(['msg'=>'bạn không phải là tác giả','status'=>false]);
        }
        $comment->fill($data)->save();
        return response()->json(['msg'=>$comment,'status'=>true]);
    }
    public function deleteComment(Request $request)
    {
        
        $this->validate($request,[
            
            'item_id'=>'numeric|required',
            'item_code'=>'string|required',
            'id'=>'numeric|required',
        ]);
        $user= auth()->user();
        if(!$user)
        {
            return response()->json(['msg'=>'chưa đăng nhập','status'=>false]);
        }
        $comment = TComment::find($request->id);
        if(!$comment)
        {
            return response()->json(['msg'=>'không tìm thấy dữ liệu','status'=>false]);
        }
        if($user->id != $comment->user_id)
        {
            return response()->json(['msg'=>'bạn không phải là tác giả','status'=>false]);
        }
        // $comment->status = 'inactive';
        $comment->delete();
        return response()->json(['msg'=>'xóa thành công','status'=>true]);
    }
    // public function addComment($item_id, $item_code,$user_id,$content,$resouces)
    // {
       
    //     $data = array();
    //     $data['item_id'] = $item_id;
    //     $data['item_code'] = $item_code;
    //     $data['user_id'] = $user_id;
    //     $data['content'] = $content;
    //     $data['resouces'] = $resouces;
    //     $comment = TComment::create($data);
    //     if($data['item_code'] == 'congviec')
    //     {
    //         $notice = array();
    //         $congviec = \App\Modules\Nguoitimviec\JCongviec::findOrFail($data['item_id']);
    //         $notice['user_id'] = $congviec->user_id;
    //         $notice['item_id'] =  $data['item_id'] ;
    //         $notice['item_code'] =  $data['item_code'] ;
    //         $user = User::find($data['user_id']);
    //         $notice['title'] =  $user->full_name .'thêm bình luận việc làm';
    //         $notice['url_view'] = route('front.vieclam.chitietvieclam',$congviec->id);
    //         TNotice::create($notice);
    //     }
    //     return $comment;
    // }

    public static function getCommentActive($item_id, $item_code )
    {
       
        // $comments = Tcomment::where('item_id',$item_id)->where('item_code',$item_code)
        //     ->where('status','active')->where('parent_id',0)->get();

        $comments = DB::table('t_comments')
        ->select ('t_comments.*','u.full_name', 'u.photo as photo' )
        ->where('item_id',$item_id)->where('item_code',$item_code)->where('status','active')->where('parent_id',0)
        ->leftJoin(\DB::raw('( select id, full_name, photo from users ) as u'),'t_comments.user_id','=','u.id')
        ->orderBy('id','ASC')->get();
		
		
        foreach($comments as $comment)
        {
            // $subcomments = Tcomment::where('item_id',$item_id)->where('item_code',$item_code)
            // ->where('status','active')->where('parent_id',$comment->id)->get();
            $subcomments = DB::table('t_comments')
            ->select ('t_comments.*','u.full_name', 'u.photo as photo' )
            ->where('item_id',$item_id)->where('item_code',$item_code)->where('status','active')->where('parent_id',$comment->id)
            ->leftJoin(\DB::raw('( select id, full_name, photo from users ) as u'),'t_comments.user_id','=','u.id')
            ->orderBy('id','ASC')->get();

            $comment->subcomments = $subcomments;
        }
        $data['item_id'] = $item_id;
        $data['item_code'] = $item_code;
        $data['comments'] = $comments;
        if(auth()->user())
        {
            $data['curuser'] = auth()->user();
        }
       
        $html = view('Tuongtac::frontend.comments.show',$data)->render();
        return $html;
    }
    
    public function statusChange($id)
    {
        $comment = TComment::findOrFail($id);
        $comment->statusChange();
    }
    public function saveCommentArr($data)
    {
        $comment = TComment::create($data);
        if($data['item_code'] == 'tblog')
        {
            $notice = array();
            $blog = TBlog::findOrFail($data['item_id']);
            $notice['user_id'] = $blog->user_id;
            $notice['item_id'] =  $data['item_id'] ;
            $notice['item_code'] =  $data['item_code'] ;
            $user = User::find($data['user_id']);
            $notice['title'] =  $user->full_name .' thêm bình luận bài viết';
            $notice['url_view'] = route('front.tblogs.show',$blog->slug);
            TNotice::create($notice);
        }
        if($data['item_code'] == 'blog')
        {
            $notice = array();
            $blog = \App\Models\Blog::findOrFail($data['item_id']);
            $notice['user_id'] = $blog->user_id;
            $notice['item_id'] =  $data['item_id'] ;
            $notice['item_code'] =  $data['item_code'] ;
            $user = User::find($data['user_id']);
            $notice['title'] =  $user->full_name .' thêm bình luận bài viết';
            $notice['url_view'] = route('front.page.view',$blog->slug);
            TNotice::create($notice);
        }
        if($data['item_code'] == 'congviec')
        {
            $notice = array();
            $congviec = JCongviec::findOrFail($data['item_id']);
            $notice['user_id'] = $congviec->user_id;
            $notice['item_id'] =  $data['item_id'] ;
            $notice['item_code'] =  $data['item_code'] ;
            $user = User::find($data['user_id']);
            $notice['title'] =  $user->full_name .' thêm bình luận việc làm';
            $notice['url_view'] = route('front.vieclam.chitietvieclam',$congviec->id);
            TNotice::create($notice);
        }
        if($data['item_code'] == 'ads')
        {
            $notice = array();
           
            $ad = \App\Models\Ads::find($data['item_id'] );
            if($ad)
            {
                $ad->position += 1;
                $ad->save();
            }
    
            $notice['user_id'] = $ad->user_id;
            $notice['item_id'] =  $data['item_id'] ;
            $notice['item_code'] =  $data['item_code'] ;
            $user = User::find($data['user_id']);
            $notice['title'] =  $user->full_name .' thêm bình luận cắt lỗ xả hàng';
            $notice['url_view'] = route('front.ad.view',$ad->slug);
            TNotice::create($notice);
        }
        return $comment;
    }
}
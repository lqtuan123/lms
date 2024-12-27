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
use  App\Modules\Tuongtac\Models\TMotion;
use  App\Modules\Tuongtac\Models\TMotionItem;
use  App\Modules\Tuongtac\Models\TRecommend;
use  App\Modules\Tuongtac\Models\TVoteItem;
use  App\Modules\Tuongtac\Models\TUserpage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
class TUserpageController extends Controller
{
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required|string',
            'new_password' => 'required|confirmed|min:8|string'
        ]);
        $auth = \Auth::user();
        // dd($request->get('current_password'));
            // The passwords matches
        if (!Hash::check($request->get('current_password'), $auth->password)) 
        {
            return back()->with('error', "Current Password is Invalid");
        }
 
        // Current password and new password same
        if (strcmp($request->get('current_password'), $request->new_password) == 0) 
        {
            return redirect()->back()->with("error", "New Password cannot be same as your current password.");
        }
 
        $user =  User::find($auth->id);
        $user->password =  Hash::make($request->new_password);
        $user->save();
      
        return redirect()->route('front.userpages.edituser')
            ->withSuccess('Bạn đã cập nhật thành công');
       
    }

     public function updateuser(Request $request)
    {
        $this->validate($request,[
            'full_name'=>'string|required',
            'address'=>'string|required',
            'photo'=>'string|nullable',
            'description'=>'string|nullable',
        ]);
        $data = $request->all();
        $user = auth()->user();
        if (!isset($data['photo']) || $data['photo']==null|| $data['photo']=='')
            $data['photo'] = $user->photo;
        $status = $user->fill($data)->save();
        
        if($status)
        {
            return redirect()->route('front.userpages.edituser')
            ->withSuccess('Bạn đã cập nhật thành công');
        }   
        else
        {
            return redirect()->route('userpages.edituser')
            ->withError('Lỗi xãy ra');
        }
    }
    public function viewuser($slug)
    {
        $userpage = TUserpage::where('slug',$slug)->first();
        if(!$userpage)
            return redirect()->back()->with('error','không tìm thấy!');
        $user = User::findOrFail($userpage->user_id);
        $data['userpage'] = $userpage;
        $data['user'] = $user;
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
        $data['pagetitle']="Thông tin cá nhân ".$user->full_name  ;
        $data['page_up_title'] = "Thông tin cá nhân ".$user->full_name  ;
        $data['page_subtitle']="Thông tin cá nhân ".$user->full_name  ;
        $data['page_title']="Thông tin cá nhân ".$user->full_name  ;
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link']= "";
        $data['page_up_title']= "Thông tin cá nhân ".$user->full_name  ;

        $data['posts']   = TBlog::where('user_id',$user->id)->paginate(20);
        $tam = \DB::select('select count(id) as tong from t_blogs where user_id ='.$user->id);
        $data['post_count'] = $tam[0]->tong;
        $data['post_recommend'] = 0;
        $tam   =\DB::table('t_blogs')
        ->select ('t_blogs.id','u.dem' )
        ->where('user_id',$user->id)
        ->leftJoin(\DB::raw('( select count(id) as dem,item_id from t_recommends where item_code="tblog" group by item_id ) as u'),'t_blogs.id','=','u.item_id')
        ->get();
        foreach ($tam as $item)
        {
            $data['post_recommend']+= $item->dem;
        }
        
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog)
        {
            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog' );
            $blog->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog' );
            
        }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        return view('Tuongtac::frontend.userpage.userpage',$data);
    }
    public function edituser( )
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
        $data['pagetitle']="Thông tin cá nhân"  ;
        $data['page_up_title'] = "Thông tin cá nhân"  ;
        $data['page_subtitle']="Thông tin cá nhân"  ;
        $data['page_title']="Thông tin cá nhân"  ;
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link']= "";
        $data['page_up_title']= "Thông tin cá nhân"  ;

        $data['profile'] = $user;
        return view('Tuongtac::frontend.userpage.profile',$data);
    }
    public function user_hornor()
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        //$data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
        $data['pagetitle']="Vinh danh người dùng"  ;
        $data['page_up_title'] = "Vinh danh người dùng"  ;
        $data['page_subtitle']="Vinh danh người dùng"  ;
        $data['page_title']="Vinh danh người dùng"  ;
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link']= "";
        $data['page_up_title']= "Vinh danh người dùng"  ;
        
        $data['item_code'] = 'user';
     
        $data['topUsers'] = \DB::table('users')
                ->leftjoin('t_userpages', 'users.id', '=','t_userpages.user_id' )
                 ->select('t_userpages.*','users.*')
                 ->orderBy('t_userpages.point','desc')
                 ->limit(4)
                 ->get();
        $data['otherUsers'] = \DB::table('users')
                ->leftjoin('t_userpages', 'users.id', '=','t_userpages.user_id' )
                  
                  ->select('t_userpages.*','users.*')
                  ->orderBy('t_userpages.point','desc')
                  ->skip(4)->take(36)->get() ;
        
        return view('Tuongtac::frontend.userpage.hornor',$data);
 
       
    }
}
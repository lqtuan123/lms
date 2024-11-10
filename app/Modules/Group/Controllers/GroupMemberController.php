<?php

namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
 
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
use Illuminate\Http\File;
use  App\Modules\Group\Models\GroupMember;
use  App\Modules\Group\Models\Group;
use  App\Modules\Group\Models\GroupRole;
class GroupMemberController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
        
    }
    public function index()
    {
    }
    public function create()
    {
    }
    public function groupMemberList($slug)
    {
        $func = "groupmember_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //

        $group =Group::where('slug',$slug)->orWhere('id',$slug)->first();
        if(!$group)
        {
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.']);
        }
        $data['active_menu']="groupmember";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item " aria-current="page"> <a href="'.route('admin.group.index').'">Nhóm </a></li>
         <li class="breadcrumb-item active" aria-current="page"> Danh sách thành viên nhóm '.$group->title.'</li>
        ';
        $data['group'] = $group;
       
        $data['groupmembers']=\DB::table('group_members')
        ->select ('group_members.*','np.full_name', 'np.photo' )
        ->where('group_id',$group->id)
        ->leftJoin(\DB::raw(' (select id, full_name, photo from users) as np '),'group_members.user_id','=','np.id')
        ->orderBy('id','ASC')->get();

        // $data['groupmembers']=GroupMember::where('group_id',$group->id)->orderBy('id','DESC')->paginate($this->pagesize);
        return view('Group::groupmember.index',$data);
    }

    public function groupmemberSearch(Request $request)
    {
        $func = "groupmember_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->datasearch)
        {
            $data['datasearch'] =$request->datasearch;
            $data['active_menu']="groupmember";
            $data['searchdata'] =$request->datasearch;
            $data['groupmembers'] = \DB::table('group_members')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.groupmember.index').'">danh sách loại nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
           
            
            return view('Group::groupmember.index',$data);
        }
        else
        {
            return redirect()->route('admin.groupmember.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    public function groupmemberStatus(Request $request)
    {
        $func = "groupmember_status";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->mode =='true')
        {
            \DB::table('group_members')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            \DB::table('group_members')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function groupAddMember($slug)
    {
        $func = "groupmember_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $group =Group::where('slug',$slug)->first();
        if(!$group)
        {
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.']);
        }
        $data['group'] = $group;
        $data['roles'] = GroupRole::where('status','active')->get();
        $data['active_menu']="groupmember";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
           <li class="breadcrumb-item " aria-current="page"> <a href="'.route('admin.group.index').'">Nhóm </a></li>
                 <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.group.members',$data['group']->slug).'">Danh sách thành viên nhóm '.$group->title.'</a></li>
      
         <li class="breadcrumb-item active" aria-current="page"> Thêm thành viên </li>
     ';
        return view('Group::groupmember.create',$data);
    }
    public function store(Request $request)
    {
        // Xác thực dữ liệu
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'group_id' => 'required|integer',
            'role' => 'required|string|max:50 ',
            'status' => 'required|in:active,inactive',
        ]);
        $func = "groupmember_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        try {
            // Lưu dữ liệu vào database
            $data= $request->all();
            $members = GroupMember::where('group_id',$data['group_id'])->where('user_id',$data['user_id'])->get();
            if(count($members) > 0)
            {
                return redirect()->route('admin.group.members',$data['group_id'])->with('success', 'Thành viên đã thuộc nhóm!');
      
            }
            GroupMember::create($data);
            // Redirect với thông báo thành công
            return redirect()->route('admin.group.members',$data['group_id'])->with('success', 'Nhóm loại đã được tạo thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.'.$e]);
        }
    }
    public function edit(string $id)
    {
        //
        $func = "groupmember_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        $data['roles'] = GroupRole::where('status','active')->get();
        $data['groupmember'] = GroupMember::findorFail($id);
        $data['user'] = \App\Models\User::findorFail($data['groupmember']->user_id);
        $data['group'] = Group::find($data['groupmember']->group_id);
        if( $data['groupmember'])
        {
            $data['active_menu']="groupmember";
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item " aria-current="page"> <a href="'.route('admin.group.index').'">Nhóm </a></li>
          
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.group.members',$data['group']->id).'">thành viên nhóm '.$data['group']->title.'</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh thành viên</li>';
            return view('Group::groupmember.edit',$data);
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
    public function update(Request $request, $id)
    {
        // Xác thực dữ liệu
        $validatedData = $request->validate([
            'user_id' => 'required|integer',
            'group_id' => 'required|integer',
            'role' => 'required|string|max:50 ',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Tìm groupmember và cập nhật
            $data = $request->all();
            $members = GroupMember::where('group_id',$data['group_id'])->where('user_id',$data['user_id'])->where('id','<>',$id)->get();
            if(count($members) > 0)
            {
                return redirect()->route('admin.group.members',$data['group_id'])->with('success', 'Thành viên đã thuộc nhóm!');
      
            }
            $groupmember = GroupMember::findOrFail($id);
            $groupmember->fill($data);
            $groupmember->save();

            // Redirect với thông báo thành công
            return redirect()->route('admin.group.members',$groupmember->group_id)->with('success', 'Cập nhật nhóm loại thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.'.$e]);
        }
    }
    public function destroy(string $id)
    {
        $func = "groupmember_delete";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        try {
            // Tìm bản ghi groupmember theo ID và xóa
            $groupmember = GroupMember::findOrFail($id);
            $group_id = $groupmember->group_id;
            $groupmember->delete();
    
            // Redirect với thông báo thành công
            return redirect()->route('admin.group.members', $group_id)->with('success', 'Nhóm loại đã được xóa thành công!');
            
        } catch (\Exception $e) {
            // Quay lại trang trước với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa dữ liệu.']);
        }
    }
}
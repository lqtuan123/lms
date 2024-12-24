<?php

namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
 
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
use Illuminate\Http\File;
use  App\Modules\Group\Models\GroupRole;
 
class GroupRoleController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
        
    }
    public function index()
    {
        $func = "grouprole_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['active_menu']="grouprole";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> vai trò nhóm </li>';
        $data['grouproles']=GroupRole::orderBy('id','DESC')->paginate($this->pagesize);
        return view('Group::grouprole.index',$data);
    }

    public function grouproleSearch(Request $request)
    {
        $func = "grouprole_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->datasearch)
        {
            $data['datasearch'] =$request->datasearch;
            $data['active_menu']="grouprole";
            $data['searchdata'] =$request->datasearch;
            $data['grouproles'] = \DB::table('group_roles')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouprole.index').'">danh sách vai trò nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            
            return view('Group::grouprole.index',$data);
        }
        else
        {
            return redirect()->route('admin.grouprole.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    public function grouproleStatus(Request $request)
    {
        $func = "grouprole_status";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->mode =='true')
        {
            \DB::table('group_roles')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            \DB::table('group_roles')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = "grouprole_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['active_menu']="grouprole";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouprole.index').'">danh sách vai trò nhóm</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo grouprole </li>';
        return view('Group::grouprole.create',$data);
    }
    public function store(Request $request)
    {
        // Xác thực dữ liệu
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50|unique:group_roles,type_code',
            'status' => 'required|in:active,inactive',
        ]);
        $func = "grouprole_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        try {
            // Lưu dữ liệu vào database
            $data= $request->all();
            GroupRole::create($data);
            // Redirect với thông báo thành công
            return redirect()->route('admin.grouprole.index')->with('success', 'Nhóm loại đã được tạo thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.']);
        }
    }
    public function edit(string $id)
    {
        //
        $func = "grouprole_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
      
        $data['grouprole'] = GroupRole::find($id);
        if( $data['grouprole'])
        {
            $data['active_menu']="grouprole";
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouprole.index').'">danh sách vai trò nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh vai trò nhóm </li>';
            return view('Group::grouprole.edit',$data);
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
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50|unique:group_roles,type_code',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Tìm grouprole và cập nhật
            $data = $request->all();
            $grouprole = GroupRole::findOrFail($id);
           $grouprole->fill($data);
            $grouprole->save();

            // Redirect với thông báo thành công
            return redirect()->route('admin.grouprole.index')->with('success', 'Cập nhật nhóm loại thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.'.$e]);
        }
    }
    public function destroy(string $id)
    {
        $func = "grouprole_delete";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        try {
            // Tìm bản ghi grouprole theo ID và xóa
            $grouprole = GroupRole::findOrFail($id);
            $grouprole->delete();
    
            // Redirect với thông báo thành công
            return redirect()->route('admin.grouprole.index')->with('success', 'Nhóm loại đã được xóa thành công!');
            
        } catch (\Exception $e) {
            // Quay lại trang trước với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa dữ liệu.']);
        }
    }
}
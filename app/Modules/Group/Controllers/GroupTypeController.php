<?php

namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
 
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
use Illuminate\Http\File;
use  App\Modules\Group\Models\GroupType;
 
class GroupTypeController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
        
    }
    public function index()
    {
        $func = "grouptype_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['active_menu']="grouptype";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Loại nhóm </li>';
        $data['grouptypes']=GroupType::orderBy('id','DESC')->paginate($this->pagesize);
        return view('Group::grouptype.index',$data);
    }

    public function grouptypeSearch(Request $request)
    {
        $func = "grouptype_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->datasearch)
        {
            $data['datasearch'] =$request->datasearch;
            $data['active_menu']="grouptype";
            $data['searchdata'] =$request->datasearch;
            $data['grouptypes'] = \DB::table('group_types')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouptype.index').'">danh sách loại nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            
            return view('Group::grouptype.index',$data);
        }
        else
        {
            return redirect()->route('admin.grouptype.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    public function grouptypeStatus(Request $request)
    {
        $func = "grouptype_status";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->mode =='true')
        {
            \DB::table('group_types')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            \DB::table('group_types')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = "grouptype_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['active_menu']="grouptype";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouptype.index').'">danh sách loại nhóm</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo grouptype </li>';
        return view('Group::grouptype.create',$data);
    }
    public function store(Request $request)
    {
        // Xác thực dữ liệu
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50|unique:group_types,type_code',
            'status' => 'required|in:active,inactive',
        ]);
        $func = "grouptype_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        try {
            // Lưu dữ liệu vào database
            $data= $request->all();
            GroupType::create($data);
            // Redirect với thông báo thành công
            return redirect()->route('admin.grouptype.index')->with('success', 'Nhóm loại đã được tạo thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.']);
        }
    }
    public function edit(string $id)
    {
        //
        $func = "grouptype_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
      
        $data['grouptype'] = GroupType::find($id);
        if( $data['grouptype'])
        {
            $data['active_menu']="grouptype";
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.grouptype.index').'">danh sách loại nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh loại nhóm </li>';
            return view('Group::grouptype.edit',$data);
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
            'type_code' => 'required|string|max:50|unique:group_types,type_code',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            // Tìm GroupType và cập nhật
            $data = $request->all();
            $groupType = GroupType::findOrFail($id);
           $groupType->fill($data);
            $groupType->save();

            // Redirect với thông báo thành công
            return redirect()->route('admin.grouptype.index')->with('success', 'Cập nhật nhóm loại thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.'.$e]);
        }
    }
    public function destroy(string $id)
    {
        $func = "grouptype_delete";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        try {
            // Tìm bản ghi GroupType theo ID và xóa
            $groupType = GroupType::findOrFail($id);
            $groupType->delete();
    
            // Redirect với thông báo thành công
            return redirect()->route('admin.grouptype.index')->with('success', 'Nhóm loại đã được xóa thành công!');
            
        } catch (\Exception $e) {
            // Quay lại trang trước với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa dữ liệu.']);
        }
    }
}
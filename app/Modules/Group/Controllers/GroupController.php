<?php

namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
 
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use  App\Modules\Group\Models\Group;
use  App\Modules\Group\Models\GroupType;
class GroupController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
        
    }
    
    public function index()
    {
        $func = "group_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['active_menu']="group";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> nhóm </li>';
        $data['groups']=Group::orderBy('id','DESC')->paginate($this->pagesize);
        return view('Group::group.index',$data);
    }

    public function groupSearch(Request $request)
    {
        $func = "group_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->datasearch)
        {
            $data['datasearch'] =$request->datasearch;
            $data['active_menu']="group";
            $data['searchdata'] =$request->datasearch;
            $data['groups'] = \DB::table('groups')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.group.index').'">danh sách nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            
            return view('Group::group.index',$data);
        }
        else
        {
            return redirect()->route('admin.group.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    public function groupStatus(Request $request)
    {
        $func = "group_status";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->mode =='true')
        {
            \DB::table('groups')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            \DB::table('groups')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $func = "group_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $data['groupTypes'] = GroupType::where('status','active')->orderBy('id','asc')->get();
        $data['active_menu']="group";
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.group.index').'">danh sách nhóm</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo grouptype </li>';
        return view('Group::group.create',$data);
    }
    public function store(Request $request)
    {
        // Xác thực dữ liệu
        $validatedData = $request->validate([
            // 'title' => 'required|string|max:255|unique:groups,title',
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50',
            'photo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);
    
        $func = "group_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        
        // Tạo slug từ title
        $slug = Str::slug($request->input('title'));
        
        // Kiểm tra xem slug đã tồn tại chưa
        $slug_count = Group::where('slug', $slug)->count();
        if ($slug_count > 0)
        {
            // Thêm một chuỗi duy nhất vào slug nếu đã tồn tại
            $slug = $slug . '-' . uniqid();
        }

        // Gán slug vào dữ liệu đã xác thực
        $validatedData['slug'] = $slug;
        
        // Mặc định is_private = 0 nếu không được chọn
        if(!isset($validatedData['is_private']))
            $validatedData['is_private'] = 0;

        // Sử dụng ảnh mặc định nếu không có ảnh
        if($request->photo == null)
            $validatedData['photo'] = asset('backend/assets/dist/images/profile-6.jpg');
        
        // Gán ID của người tạo group
        $validatedData['author_id'] = auth()->user()->id;
        
        // Khởi tạo các trường JSON nếu cần
        $validatedData['members'] = json_encode([]);
        $validatedData['pending_members'] = json_encode([]);
        
        // Đảm bảo description không null
        if(!isset($validatedData['description']))
            $validatedData['description'] = '';
        
        // Save data to the database
        try {
            // Lưu dữ liệu vào database
            Group::create($validatedData);

            // Redirect với thông báo thành công
            return redirect()->route('admin.group.index')->with('success', 'Nhóm đã được tạo thành công!');
            
        } catch (\Exception $e) {
            // Log lỗi để debug
            \Log::error('Lỗi khi tạo group: ' . $e->getMessage());
            
            // Quay lại form với thông báo lỗi
            return redirect()->back()->withInput()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu: ' . $e->getMessage()]);
        }
    }
    public function edit(string $id)
    {
        //
        $func = "group_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        $data['groupTypes'] = GroupType::where('status','active')->orderBy('id','asc')->get();
        $data['group'] = Group::findOrFail($id);
        if( $data['group'])
        {
            $data['active_menu']="group";
            $data['breadcrumb'] = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('admin.group.index').'">danh sách nhóm</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh nhóm </li>';
            return view('Group::group.edit',$data);
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
            'type_code' => 'required|string|max:50',
            'photo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $func = "group_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        $slug = Str::slug($request->input('title'));
        $slug_count = Group::where('slug',$slug)->where('id','<>',$id)->count();
        if ( $slug_count > 0)
        {
            return redirect()->back()->withInput()->withErrors(['error' => 'Tên nhóm đã có, cần đổi tên!']);
        }
        $group = Group::findOrFail($id);
        $validatedData['slug'] =$slug;
        if(!isset($validatedData['is_private']))
            $validatedData['is_private'] = 0;

        if($request->photo == null)
            $validatedData['photo'] = $group->photo;
         
        try {
            // Tìm GroupType và cập nhật
           
            $group->fill($validatedData);
            $group->save();

            // Redirect với thông báo thành công
            return redirect()->route('admin.group.index')->with('success', 'Cập nhật nhóm loại thành công!');
            
        } catch (\Exception $e) {
            // Quay lại form với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi lưu dữ liệu.'.$e]);
        }
    }
    public function destroy(string $id)
    {
        $func = "group_delete";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        try {
            // Tìm bản ghi GroupType theo ID và xóa
            $group  = Group::findOrFail($id);
            $group->delete();
    
            // Redirect với thông báo thành công
            return redirect()->route('admin.group.index')->with('success', 'Nhóm loại đã được xóa thành công!');
            
        } catch (\Exception $e) {
            // Quay lại trang trước với thông báo lỗi nếu có lỗi
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa dữ liệu.']);
        }
    }
}
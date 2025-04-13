<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    //
    protected $pagesize;
    public function __construct( )
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('auth');
        
    }
    public function store_blog_tag($blog_id,$tag_ids)
    {
        if(!$tag_ids || count($tag_ids) == 0)
            return;
        foreach($tag_ids as $tag_id)
        {
            $tag = Tag::find($tag_id);
            if(!$tag)
            {
                $datatag['title'] = $tag_id;
                $slug = Str::slug( $datatag['title'] );
                $slug_count = Tag::where('slug',$slug)->count();
                if($slug_count > 0)
                {
                    $slug .= time().'-'.$slug;
                }
                $datatag['slug'] = $slug;
                
                $tag = Tag::create($datatag);
                sleep(1);
            }
            $data['tag_id'] = $tag->id;
            $data['blog_id'] = $blog_id;
            \App\Models\TagBlog::create($data);
            $tag->hit += 1;
            $tag->save();
        }
    }

    public function update_blog_tag($blog_id,$tag_ids)
    {
        $sql = "delete from tag_blogs where blog_id = ".$blog_id;
        DB::select($sql);
         $this->store_blog_tag($blog_id,$tag_ids);
    }
    public function update_product_tag($product_id,$tag_ids)
    {
        $sql = "delete from tag_products where product_id = ".$product_id;
        DB::select($sql);
         $this->store_product_tag($product_id,$tag_ids);
    }
    public function store_product_tag($product_id,$tag_ids)
    {
        if(!$tag_ids || count($tag_ids) == 0)
            return;
        foreach($tag_ids as $tag_id)
        {
            $tag = Tag::find($tag_id);
            if(!$tag)
            {
                $datatag['title'] = $tag_id;
                $slug = Str::slug( $datatag['title'] );
                $slug_count = Tag::where('slug',$slug)->count();
                if($slug_count > 0)
                {
                    $slug .= time().'-'.$slug;
                }
                $datatag['slug'] = $slug;
                
                $tag = Tag::create($datatag);

            }
            $data['tag_id'] = $tag->id;
            $data['product_id'] = $product_id;
            \App\Models\TagProduct::create($data);
            $tag->hit += 1;
            $tag->save();
            sleep(1);
        }
    }
    public function store_resource_tag($resource_id,$tag_ids)
    {
        if(!$tag_ids || count($tag_ids) == 0)
            return;
        foreach($tag_ids as $tag_id)
        {
            $tag = Tag::find($tag_id);
            if(!$tag)
            {
                $datatag['title'] = $tag_id;
                $slug = Str::slug( $datatag['title'] );
                $slug_count = Tag::where('slug',$slug)->count();
                if($slug_count > 0)
                {
                    $slug .= time().'-'.$slug;
                }
                $datatag['slug'] = $slug;
                
                $tag = Tag::create($datatag);
                sleep(1);
            }
            $data['tag_id'] = $tag->id;
            $data['resource_id'] = $resource_id;
            \App\Modules\Resource\Models\TagResource::create($data);
            $tag->hit += 1;
            $tag->save();
        }
    }

    public function update_resource_tag($resource_id,$tag_ids)
    {
        $sql = "delete from tag_resources where resource_id = ".$resource_id;
        DB::select($sql);
         $this->store_resource_tag($resource_id,$tag_ids);
    }
    public function store_book_tag($book_id,$tag_ids)
    {
        if(!$tag_ids || count($tag_ids) == 0)
            return;
        
        // Lấy danh sách tag_ids hiện tại của book để tránh trùng lặp
        $existingTagIds = DB::table('tag_books')
            ->where('book_id', $book_id)
            ->pluck('tag_id')
            ->toArray();
        
        foreach($tag_ids as $tag_id)
        {
            // Kiểm tra nếu là số (ID) hoặc chuỗi (title)
            if (is_numeric($tag_id)) {
                $tag = Tag::find($tag_id);
            } else {
                // Nếu là chuỗi, tìm theo title
                $tag = Tag::where('title', $tag_id)->first();
            }
            
            if(!$tag)
            {
                // Tạo tag mới
                $datatag['title'] = $tag_id;
                $slug = Str::slug($datatag['title']);
                
                // Tạo slug unique
                $originalSlug = $slug;
                $counter = 1;
                while (Tag::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                
                $datatag['slug'] = $slug;
                $datatag['status'] = 'active';
                $datatag['hit'] = 1;
                
                $tag = Tag::create($datatag);
            }
            
            // Kiểm tra nếu tag đã tồn tại cho book này
            if (!in_array($tag->id, $existingTagIds)) {
                $data['tag_id'] = $tag->id;
                $data['book_id'] = $book_id;
                \App\Modules\Book\Models\TagBook::create($data);
                
                // Tăng hit
                $tag->hit += 1;
                $tag->save();
            }
        }
    }
    public function update_book_tag($book_id,$tag_ids)
    {
        $sql = "delete from tag_books where book_id = ".$book_id;
        DB::select($sql);
         $this->store_book_tag($book_id,$tag_ids);
    }

        // event tag

        public function store_event_tag($event_id, $tag_ids)
        {
            if (!$tag_ids || count($tag_ids) == 0) {
                return;
            }
        
            foreach ($tag_ids as $tag_id) {
                $tag = Tag::find($tag_id);
                if (!$tag) {
                    $datatag['title'] = $tag_id;
                    $slug = Str::slug($datatag['title']);
                    $slug_count = Tag::where('slug', $slug)->count();
                    if ($slug_count > 0) {
                        $slug .= time() . '-' . $slug;
                    }
                    $datatag['slug'] = $slug;
        
                    $tag = Tag::create($datatag);
                    sleep(1);
                }
        
                $data['tag_id'] = $tag->id;
                $data['event_id'] = $event_id;
                \App\Modules\Events\Models\TagEvent::create($data);
        
                $tag->hit += 1;
                $tag->save();
            }
        }
        
        public function update_event_tag($event_id, $tag_ids)
        {
            $sql = "delete from tag_events where event_id = " . $event_id;
            DB::select($sql);
        
            $this->store_event_tag($event_id, $tag_ids);
        }

        
    public function index()
    {
        $func = "tag_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $active_menu="tag_list";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tags </li>';
        $tags=Tag::orderBy('id','DESC')->paginate($this->pagesize);
        return view('backend.tags.index',compact('tags','breadcrumb','active_menu'));
    }
    public function tagSearch(Request $request)
    {
        $func = "tag_list";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->datasearch)
        {
            $active_menu="tag_list";
            $searchdata =$request->datasearch;
            $tags = DB::table('tags')->where('title','LIKE','%'.$request->datasearch.'%')
            ->paginate($this->pagesize)->withQueryString();
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('tag.index').'">tags</a></li>
            <li class="breadcrumb-item active" aria-current="page"> tìm kiếm </li>';
            
            return view('backend.tags.search',compact('tags','breadcrumb','searchdata','active_menu'));
        }
        else
        {
            return redirect()->route('tag.index')->with('success','Không có thông tin tìm kiếm!');
        }

    }
    public function tagStatus(Request $request)
    {
        $func = "tag_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        if($request->mode =='true')
        {
            DB::table('tags')->where('id',$request->id)->update(['status'=>'active']);
        }
        else
        {
            DB::table('tags')->where('id',$request->id)->update(['status'=>'inactive']);
        }
        return response()->json(['msg'=>"Cập nhật thành công",'status'=>true]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $func = "tag_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        $active_menu="tag_add";
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item  " aria-current="page"><a href="'.route('tag.index').'">tags</a></li>
        <li class="breadcrumb-item active" aria-current="page"> tạo tags </li>';
        return view('backend.tags.create',compact('breadcrumb','active_menu'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $func = "tag_add";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        // return $request->all();
        $this->validate($request,[
            'title'=>'string|required',
            'status'=>'nullable|in:active,inactive',
        ]);
        $data = $request->all();
        $slug = Str::slug($request->input('title'));
        $slug_count = Tag::where('slug',$slug)->count();
        if($slug_count > 0)
        {
            $slug .= time().'-'.$slug;
        }
        $data['slug'] = $slug;
        
        $status = Tag::create($data);
        if($status){
            return redirect()->route('tag.index')->with('success','Tạo tag thành công!');
        }
        else
        {
            return back()->with('error','Có lỗi xãy ra!');
        }    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $func = "tag_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        $tag = Tag::find($id);
        if($tag)
        {
            $active_menu="tag_list";
            $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item  " aria-current="page"><a href="'.route('tag.index').'">tags</a></li>
            <li class="breadcrumb-item active" aria-current="page"> điều chỉnh tags </li>';
            return view('backend.tags.edit',compact('breadcrumb','tag','active_menu'));
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $func = "tag_edit";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $tag = Tag::find($id);
        if($tag)
        {
            $this->validate($request,[
                'title'=>'string|required',
                'status'=>'nullable|in:active,inactive',
            ]);
            $data = $request->all();
            $status = $tag->fill($data)->save();
            if($status){
                return redirect()->route('tag.index')->with('success','Cập nhật thành công');
            }
            else
            {
                return back()->with('error','Something went wrong!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
      
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $func = "tag_delete";
        if(!$this->check_function($func))
        {
            return redirect()->route('unauthorized');
        }
        //
        $tag = Tag::find($id);
        if($tag)
        {
            $status = $tag->delete();
            if($status){
                return redirect()->route('tag.index')->with('success','Xóa tag thành công!');
            }
            else
            {
                return back()->with('error','Có lỗi xãy ra!');
            }    
        }
        else
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
    }
}

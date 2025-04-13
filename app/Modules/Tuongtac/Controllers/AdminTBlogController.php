<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tuongtac\Models\TBlog;
use App\Modules\Tuongtac\Models\TTag;
use App\Modules\Resource\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminTBlogController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->pagesize = env('NUMBER_PER_PAGE', '20');
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = TBlog::with('author');

        // Tìm kiếm theo tiêu đề
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo tác giả
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $blogs = $query->orderBy('created_at', 'desc')->paginate($this->pagesize);

        // Thêm thông tin tag và tài nguyên vào từng bài viết
        foreach ($blogs as $blog) {
            $blog->tags = DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog')
                ->select('t_tags.*')
                ->get();

            if ($blog->resources) {
                if (is_string($blog->resources)) {
                    $resourceIds = trim($blog->resources, '[]');
                    $resourceIds = explode(',', $resourceIds);
                } else {
                    $resourceIds = $blog->resources;
                }
                
                if (!empty($resourceIds)) {
                    $blog->resource_files = Resource::whereIn('id', $resourceIds)->get();
                }
            }
        }

        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý bài viết</li>';
        $active_menu = "tblog_list";

        return view('Tuongtac::admin.blogs.index', compact('blogs', 'breadcrumb', 'active_menu'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tạo bài viết</li>';
        $active_menu = "tblog_add";
        $tags = TTag::orderBy('title', 'ASC')->get();
        
        return view('Tuongtac::admin.blogs.create', compact('breadcrumb', 'active_menu', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'photo' => 'nullable|string',
            'tags' => 'nullable|array',
            'document' => 'nullable|array',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:20480',
            'status' => 'required|in:1,0',
        ]);

        // Xử lý ảnh từ Dropzone
        if ($request->photo) {
            $photo = $request->photo;
        } else {
            $photo = null;
        }

        // Xử lý slug
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (TBlog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Xử lý tài liệu đính kèm
        $resourceIds = [];
        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $resourceIds[] = Resource::createResource($request, $file, 'TBlog')->id;
            }
        }

        // Thêm URLs nếu có
        if ($request->has('urls') && $request->urls) {
            foreach ($request->urls as $url) {
                if ($url) {
                    $resourceIds[] = Resource::createUrlResource(uniqid(), $url, 'other', 'tblog')->id;
                }
            }
        }

        // Tạo bài viết mới
        $blog = TBlog::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'photo' => $photo,
            'user_id' => auth()->id(),
            'hit' => 0,
            'status' => $request->status,
            'resources' => $resourceIds,
        ]);

        // Xử lý tags
        $tag_ids = $request->tags;
        $tagcontroller = new TTagController();
        $tagcontroller->store_item_tag($blog->id, $tag_ids, 'tblog');

        return redirect()->route('admin.tblogs.index')->with('success', 'Bài viết đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $blog = TBlog::with('author')->findOrFail($id);
        
        // Lấy tags
        $blog->tags = DB::table('t_tags')
            ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
            ->where('t_tag_items.item_id', $blog->id)
            ->where('t_tag_items.item_code', 'tblog')
            ->select('t_tags.*')
            ->get();

        // Lấy tài nguyên đính kèm
        if ($blog->resources) {
            if (is_string($blog->resources)) {
                $resourceIds = trim($blog->resources, '[]');
                $resourceIds = explode(',', $resourceIds);
            } else {
                $resourceIds = $blog->resources;
            }
            
            if (!empty($resourceIds)) {
                $blog->resource_files = Resource::whereIn('id', $resourceIds)->get();
            }
        }

        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết bài viết</li>';
        $active_menu = "tblog_view";

        return view('Tuongtac::admin.blogs.show', compact('blog', 'breadcrumb', 'active_menu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blog = TBlog::findOrFail($id);
        $tags = TTag::orderBy('title', 'ASC')->get();
        
        // Lấy tag_ids đã được gán cho bài viết
        $tag_ids = DB::table('t_tag_items')
            ->where('item_id', $blog->id)
            ->where('item_code', 'tblog')
            ->pluck('tag_id')
            ->toArray();

        // Lấy tài nguyên đính kèm
        if ($blog->resources) {
            if (is_string($blog->resources)) {
                $resourceIds = trim($blog->resources, '[]');
                $resourceIds = explode(',', $resourceIds);
            } else {
                $resourceIds = $blog->resources;
            }
            
            if (!empty($resourceIds)) {
                $resources = Resource::whereIn('id', $resourceIds)->get();
            } else {
                $resources = collect([]);
            }
        } else {
            $resources = collect([]);
        }

        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa bài viết</li>';
        $active_menu = "tblog_edit";

        return view('Tuongtac::admin.blogs.edit', compact('blog', 'tags', 'tag_ids', 'resources', 'breadcrumb', 'active_menu'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $blog = TBlog::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'photo' => 'nullable|string',
            'tags' => 'nullable|array',
            'document' => 'nullable|array',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:20480',
            'status' => 'required|in:1,0',
        ]);

        // Cập nhật ảnh từ Dropzone
        if ($request->photo) {
            $blog->photo = $request->photo;
        }

        // Cập nhật slug nếu tiêu đề thay đổi
        if ($request->title !== $blog->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            while (TBlog::where('slug', $slug)->where('id', '!=', $blog->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $blog->slug = $slug;
        }

        // Xử lý tài liệu đính kèm mới
        $existingResources = is_array($blog->resources) ? $blog->resources : [];
        $newResourceIds = [];

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $newResourceIds[] = Resource::createResource($request, $file, 'TBlog')->id;
            }
        }

        // Thêm URLs mới nếu có
        if ($request->has('urls') && $request->urls) {
            foreach ($request->urls as $url) {
                if ($url) {
                    $newResourceIds[] = Resource::createUrlResource(uniqid(), $url, 'other', 'tblog')->id;
                }
            }
        }

        // Kết hợp tài nguyên cũ và mới
        $resourceIds = array_merge($existingResources, $newResourceIds);
        
        // Cập nhật thông tin bài viết
        $blog->title = $request->title;
        $blog->content = $request->content;
        $blog->status = $request->status;
        $blog->resources = $resourceIds;
        $blog->save();

        // Cập nhật tags
        $tag_ids = $request->tags;
        $tagcontroller = new TTagController();
        $tagcontroller->update_item_tag($blog->id, $tag_ids, 'tblog');

        return redirect()->route('admin.tblogs.index')->with('success', 'Bài viết đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = TBlog::findOrFail($id);

        // Xóa các liên kết tags
        DB::table('t_tag_items')
            ->where('item_id', $blog->id)
            ->where('item_code', 'tblog')
            ->delete();

        // Xóa tương tác, bình luận và các liên kết khác nếu cần
        
        // Xóa bài viết
        $blog->delete();

        return redirect()->route('admin.tblogs.index')->with('success', 'Bài viết đã được xóa thành công.');
    }

    /**
     * Toggle the status of a blog post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function blogStatus(Request $request)
    {
        $blog = TBlog::findOrFail($request->id);
        $blog->status = $blog->status ? 0 : 1;
        $blog->save();

        return response()->json(['status' => true, 'msg' => 'Trạng thái đã được cập nhật']);
    }

    /**
     * Remove a resource from a blog post.
     *
     * @param  int  $blogId
     * @param  int  $resourceId
     * @return \Illuminate\Http\Response
     */
    public function removeResource($blogId, $resourceId)
    {
        $blog = TBlog::findOrFail($blogId);
        
        // Lấy danh sách tài nguyên hiện tại
        $resources = is_array($blog->resources) ? $blog->resources : [];
        
        // Loại bỏ tài nguyên cần xóa
        $updatedResources = array_filter($resources, function($id) use ($resourceId) {
            return $id != $resourceId;
        });
        
        // Cập nhật lại bài viết
        $blog->resources = array_values($updatedResources);
        $blog->save();
        
        return redirect()->back()->with('success', 'Tài nguyên đã được xóa khỏi bài viết.');
    }
}

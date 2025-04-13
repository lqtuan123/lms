<?php

namespace App\Modules\Book\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Modules\Book\Models\Book;
use App\Modules\Book\Models\BookType;
use App\Modules\Resource\Models\Resource;
use App\Modules\Resource\Models\ResourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Comment;


class BookController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->pagesize = env('NUMBER_PER_PAGE', '20');
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Book::with('user', 'bookType');

        // Lọc theo loại sách nếu có
        if ($request->filled('type_id')) {
            $query->where('book_type_id', $request->type_id);
        }

        $books = $query->paginate($this->pagesize);

        foreach ($books as $book) {
            $resourceIds = $book->resources['resource_ids'] ?? [];
            $resourceUrls = Resource::whereIn('id', $resourceIds)->pluck('url')->toArray();
            $book->setAttribute('resource_urls', $resourceUrls);
        }
        

        $bookTypes = BookType::all(); // lấy danh sách loại sách để hiển thị dropdown

        $breadcrumb = '
    <li class="breadcrumb-item"><a href="#">/</a></li>
    <li class="breadcrumb-item active" aria-current="page">Danh sách sách</li>';
        $active_menu = "book_list";
        $selected_type = $request->type_id;

        return view('Book::books.index', compact('books', 'bookTypes', 'breadcrumb', 'active_menu', 'selected_type'));
    }

    public function create()
    {
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tạo sách</li>';
        $active_menu = "book_add";
        $tags = Tag::where('status', 'active')->orderBy('title', 'ASC')->get();
        //$typeCodes = ResourceType::distinct()->pluck('code');
        $bookTypes = BookType::all();

        return view('Book::books.create', compact('breadcrumb', 'active_menu',  'tags', 'bookTypes'));
    }

    public function store(Request $request)
    {
        // Debug dữ liệu gửi lên
        // dd($request->all());

        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'summary' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'document' => 'required|array',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx|max:204800',
            'status' => 'required|in:active,inactive',
            'tag_ids' => 'nullable|array',
            'book_type_id' => 'required|exists:book_types,id',
        ]);

        // Sau phần validation, trước khi tạo book
        if ($request->photo) {
            $photo = trim($request->photo, '[]"');
        } else {
            $photo = null;
        }

        // Xử lý tài liệu (document)
        $resourceIds = [];

        foreach ($request->file('document') as $file) {
            $resource = Resource::createResource($request, $file, 'Book');
            if ($resource) {
                $resourceIds[] = $resource->id;
            }
        }

        // Xử lý slug
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (Book::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Lưu sách vào database
        $book = Book::create([
            'title' => $request->title,
            'slug' => $slug,
            'photo' => $photo,  // Sử dụng biến đã được xử lý
            'summary' => $request->summary,
            'content' => $request->content,
            'status' => $request->status,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'book_type_id' => $request->book_type_id,
            'resources' => json_encode([
                'book_id' => null, // Will be updated after creation
                'resource_ids' => $resourceIds,
            ])
        ]);

        // Cập nhật book_id trong resources
        $resourcesData = json_decode($book->resources, true);
        $resourcesData['book_id'] = $book->id;
        $book->resources = json_encode($resourcesData);
        $book->save();

        // Gắn thẻ (tags)
        if ($request->tag_ids) {
            (new \App\Http\Controllers\TagController())->store_book_tag($book->id, $request->tag_ids);
        }

        return redirect()->route('admin.books.index')->with('success', 'Tạo sách thành công.');
    }


    public function edit($id)
    {
        $book = Book::with('user')->findOrFail($id);
        $tags = Tag::where('status', 'active')->orderBy('title', 'ASC')->get();
        $tag_ids = DB::table('tag_books')->where('book_id', $book->id)->pluck('tag_id')->toArray();
        $resourceIds = json_decode($book->resources, true)['resource_ids'] ?? [];
        $resources = Resource::whereIn('id', $resourceIds)->get();
        $bookTypes = BookType::all();

        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa sách</li>';
        $active_menu = "book_edit";

        return view('Book::books.edit', compact('book', 'tags', 'tag_ids', 'resources', 'breadcrumb', 'active_menu', 'bookTypes'));
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'summary' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'document' => 'nullable|array',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx|max:204800',
            'status' => 'required|in:active,inactive',
            'book_type_id' => 'required|exists:book_types,id',
        ]);

        // Cập nhật slug nếu title thay đổi
        if ($request->title !== $book->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            while (Book::where('slug', $slug)->where('id', '!=', $book->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        } else {
            $slug = $book->slug;
        }

        // Cập nhật ảnh bìa từ Dropzone (chỉ thay đổi nếu có URL mới)
        if ($request->photo) {
            $photo = trim($request->photo, '[]"');
            $book->photo = $photo;
        }

        // Xử lý tài liệu (document)
        $currentResources = [];
        if (!empty($book->resources)) {
            if (is_string($book->resources)) {
                $currentResources = json_decode($book->resources, true);
            } else {
                $currentResources = $book->resources;
            }
        }
        
        $existingResourceIds = $currentResources['resource_ids'] ?? [];
        $newResourceIds = [];

        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $resource = Resource::createResource($request, $file, 'Book');
                if ($resource) {
                    $newResourceIds[] = $resource->id;
                }
            }
        }

        // Hợp nhất danh sách tài liệu
        $finalResourceIds = array_unique(array_merge($existingResourceIds, $newResourceIds));

        // Cập nhật sách
        $book->update([
            'title' => $request->title,
            'slug' => $slug,
            'summary' => $request->summary,
            'content' => $request->content,
            'status' => $request->status,
            'book_type_id' => $request->book_type_id,
        ]);

        // Lưu tài liệu liên quan
        $book->resources = json_encode([
            'book_id' => $book->id,
            'resource_ids' => $finalResourceIds,
        ]);
        $book->save();

        // Cập nhật tags
        if ($request->has('tag_ids')) {
            (new \App\Http\Controllers\TagController())->update_book_tag($book->id, $request->tag_ids);
        }

        return redirect()->route('admin.books.index')->with('success', 'Cập nhật sách thành công.');
    }

    public function removeResource(Request $request, $bookId, $resourceId)
    {
        $resource = Resource::findOrFail($resourceId);
        if (file_exists(public_path($resource->url))) {
            unlink(public_path($resource->url));
        }
        $resource->delete();

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        if ($book->photo) {
            Storage::disk('public')->delete($book->photo);
        }
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Xóa sách thành công.');
    }

    public function bookStatus(Request $request)
    {

        $status = $request->mode == 'true' ? 'active' : 'inactive';
        DB::table('books')->where('id', $request->id)->update(['status' => $status]);

        return response()->json(['msg' => "Cập nhật thành công", 'status' => true]);
    }
    public function bookSearch(Request $request)
    {
        $query = Book::with('user', 'bookType');
        
        // Tìm kiếm theo tên
        if ($request->filled('datasearch')) {
            $query->where('title', 'like', '%' . $request->datasearch . '%');
        }
        
        // Lọc theo loại sách nếu có
        if ($request->filled('type_id')) {
            $query->where('book_type_id', $request->type_id);
        }

        $books = $query->paginate($this->pagesize);

        // Lấy URL tài nguyên cho mỗi sách
        foreach ($books as $book) {
            $resourceIds = [];
            
            if (!empty($book->resources)) {
                if (is_string($book->resources)) {
                    $resourcesData = json_decode($book->resources, true);
                    $resourceIds = $resourcesData['resource_ids'] ?? [];
                } else if (is_array($book->resources) && isset($book->resources['resource_ids'])) {
                    $resourceIds = $book->resources['resource_ids'];
                }
            }
            
            $resourceUrls = Resource::whereIn('id', $resourceIds)->pluck('url')->toArray();
            $book->setAttribute('resource_urls', $resourceUrls);
        }
        
        $bookTypes = BookType::all();
        $breadcrumb = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tìm kiếm sách</li>';
        $active_menu = "book_list";
        $selected_type = $request->type_id;

        if ($books->isNotEmpty()) {
            return view('Book::books.index', compact('books', 'bookTypes', 'breadcrumb', 'active_menu', 'selected_type'));
        } else {
            return back()->with('error', 'Không tìm thấy sách nào.');
        }
    }

    public function show($id)
    {
        // Tìm sách theo id
        $book = Book::with('user', 'bookType')->findOrFail($id);

        // Lấy tài nguyên liên quan
        $resourceIds = json_decode($book->resources, true)['resource_ids'] ?? [];
        $resources = Resource::whereIn('id', $resourceIds)->get();
        $comments = Comment::where('book_id', $id)->with('user')->latest()->get();

        // Lấy các tag gắn với sách
        $tags = DB::table('tag_books')->where('book_id', $book->id)->pluck('tag_id');
        $tagNames = Tag::whereIn('id', $tags)->pluck('title');

        // Lấy thông tin các loại tài liệu (nếu cần)
        $bookTypes = BookType::all();

        // Chuẩn bị breadcrumb và menu hiện tại
        $breadcrumb = '
    <li class="breadcrumb-item"><a href="#">/</a></li>
    <li class="breadcrumb-item"><a href="' . route('admin.books.index') . '">Danh sách sách</a></li>
    <li class="breadcrumb-item active" aria-current="page">Chi tiết sách</li>';

        $active_menu = "book_show";

        // Trả về view hiển thị chi tiết sách
        return view('Book::books.show', compact('book', 'resources', 'tags', 'tagNames', 'breadcrumb', 'active_menu', 'bookTypes', 'comments'));
    }

    public function toggleBlock($id)
    {
        $book = Book::findOrFail($id);
        $book->block = $book->block === 'yes' ? 'no' : 'yes';
        $book->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái sách thành công.');
    }
}

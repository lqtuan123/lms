<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Modules\Book\Models\Book;
use App\Modules\Book\Models\BookType;
use App\Modules\Resource\Models\Resource;
use App\Providers\RecentBooksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class UserBookController extends Controller
{
    protected $recentBooksService;
    public function __construct(RecentBooksService $recentBooksService)
    {
        $this->middleware('auth'); // Yêu cầu đăng nhập
        $this->recentBooksService = $recentBooksService;
    }

    // Danh sách sách người dùng đã đăng
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Book::where('user_id', $user->id);

        // Xử lý tìm kiếm theo tiêu đề
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $books = $query->with(['user', 'bookType', 'tags'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $booktypes = BookType::withCount('books')
            ->where('status', 'active')
            ->get();

        // Thêm thông tin trạng thái cho view
        $statuses = [
            'all' => 'Tất cả',
            'active' => 'Đang hoạt động',
            'inactive' => 'Không hoạt động'
        ];

        $blockStatuses = [
            'all' => 'Tất cả',
            'true' => 'Đã bị chặn',
            'false' => 'Không bị chặn'
        ];

        $featuredBooks = Book::with('user')
            ->limit(5)
            ->get();
        $recommendedBooks = Book::where('views', '>', 0)
            ->orderBy('views', 'desc')
            ->limit(6)
            ->get();

        // Lấy sách vừa đọc
        $recentBooks = $this->recentBooksService->getRecentBooks();
        return view('frontend.book.userbook.index', compact(
            'books',
            'booktypes',
            'statuses',
            'blockStatuses',
            'featuredBooks',
            'recommendedBooks',
            'recentBooks'
        ));
    }

    // Hiển thị form chỉnh sửa sách
    public function edit($id)
    {
        $book = Book::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Kiểm tra nếu sách bị block
        if ($book->block === 'yes') {
            return redirect()->route('user.books.index')
                ->with('error', 'Sách này đã bị chặn, không thể chỉnh sửa.');
        }

        $bookTypes = BookType::all();
        $tags = Tag::where('status', 'active')->get();
        
        // Lấy danh sách tag_id đã chọn cho sách này
        $selectedTags = DB::table('tag_books')
            ->where('book_id', $id)
            ->pluck('tag_id')
            ->toArray();

        return view('frontend.book.userbook.edit', compact('book', 'bookTypes', 'tags', 'selectedTags'));
    }

    // Cập nhật sách
    public function update(Request $request, $id)
    {
        $book = Book::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Kiểm tra nếu sách bị block
        if ($book->block === 'yes') {
            return redirect()->route('user.books.index')
                ->with('error', 'Sách này đã bị chặn, không thể cập nhật.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|string',
            'summary' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'book_type_id' => 'required|exists:book_types,id',
        ]);

        // Xử lý ảnh
        $photo = $book->photo; // Giữ nguyên ảnh cũ nếu không có ảnh mới
        if ($request->photo && $request->photo !== $book->photo) {
            // Xóa file ảnh cũ
            if ($book->photo) {
                $oldPhotoPath = public_path($book->photo);
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Xử lý đường dẫn ảnh mới
            $photo = $request->photo;
            // Chỉ thêm dấu / nếu không phải là URL đầy đủ
            if (!filter_var($photo, FILTER_VALIDATE_URL) && !Str::startsWith($photo, '/')) {
                $photo = '/' . $photo;
            }
        }

        // Cập nhật thông tin sách
        $book->update([
            'title' => $request->title,
            'photo' => $photo,
            'summary' => $request->summary,
            'content' => $request->content,
            'status' => $request->status,
            'book_type_id' => $request->book_type_id,
        ]);

        // Xử lý tài liệu mới nếu có
        if ($request->hasFile('document')) {
            // Lấy resource_ids hiện tại
            $currentResources = [];
            
            if (!empty($book->resources)) {
                if (is_string($book->resources)) {
                    $currentResources = json_decode($book->resources, true);
                } else {
                    $currentResources = $book->resources;
                }
            }
            
            $currentResourceIds = $currentResources['resource_ids'] ?? [];
            $resourceIds = [];

            // Tạo tài liệu mới
            foreach ($request->file('document') as $file) {
                $resource = Resource::createResource($request, $file, 'Book');
                if ($resource) {
                    $resourceIds[] = $resource->id;
                }
            }

            // Kiểm tra nếu người dùng chọn thay thế tất cả tài liệu cũ
            if ($request->has('replace_documents')) {
                // Xóa tất cả tài liệu cũ
                foreach ($currentResourceIds as $oldResourceId) {
                    $oldResource = Resource::find($oldResourceId);
                    if ($oldResource) {
                        // Xóa file vật lý nếu có
                        if ($oldResource->url) {
                            $oldFilePath = public_path($oldResource->url);
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }
                        // Xóa record trong database
                        $oldResource->delete();
                    }
                }
                
                // Chỉ sử dụng tài liệu mới
                $allResourceIds = $resourceIds;
            } else {
                // Gộp resource_ids cũ và mới nếu không chọn thay thế
                $allResourceIds = array_merge($currentResourceIds, $resourceIds);
            }

            // Cập nhật resources
            $book->resources = json_encode([
                'book_id' => $book->id,
                'resource_ids' => array_unique($allResourceIds),
            ]);
            $book->save();
        }

        // Cập nhật tags nếu có
        if ($request->has('tag_ids')) {
            (new \App\Http\Controllers\TagController())->update_book_tag($book->id, $request->tag_ids);
        }

        return redirect()->route('front.profile')->with('success', 'Sách đã được cập nhật thành công!')->withFragment('books');
    }

    // Xóa sách
    public function destroy($id)
    {
        $book = Book::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Kiểm tra nếu sách bị block
        if ($book->block === 'yes') {
            return redirect()->route('user.books.index')
                ->with('error', 'Sách này đã bị chặn, không thể xóa.');
        }

        DB::transaction(function () use ($book) {
            $book->delete();
        });

        return redirect()->route('front.profile')->with('success', 'Sách đã được xóa thành công!')->withFragment('books');
    }


    public function toggleStatus(Request $request, $id): JsonResponse
    {
        $book = Book::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Kiểm tra nếu sách bị block
        if ($book->block === 'yes') {
            return response()->json([
                'success' => false,
                'msg' => 'Sách này đã bị chặn, không thể thay đổi trạng thái.'
            ], 403);
        }

        $book->status = $book->status === 'active' ? 'inactive' : 'active';
        $book->save();

        return response()->json(['msg' => 'Trạng thái đã được cập nhật!']);
    }

    // Xóa tài liệu đính kèm
    public function deleteResource($resourceId)
    {
        try {
            // Tìm resource
            $resource = Resource::findOrFail($resourceId);

            // Tìm sách chứa resource này
            $books = Book::where('user_id', Auth::id())
                ->get()
                ->filter(function ($book) use ($resourceId) {
                    if (empty($book->resources)) return false;
                    
                    $resources = is_string($book->resources) 
                        ? json_decode($book->resources, true) 
                        : $book->resources;
                        
                    return isset($resources['resource_ids']) &&
                        in_array($resourceId, $resources['resource_ids']);
                });

            if ($books->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tài liệu hoặc bạn không có quyền xóa'
                ], 404);
            }

            $book = $books->first();

            // Xóa file vật lý nếu có
            if ($resource->url) {
                $filePath = public_path($resource->url);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Xóa record trong database
            $resource->delete();

            // Cập nhật lại danh sách resource_ids của sách
            $resources = is_string($book->resources) 
                ? json_decode($book->resources, true) 
                : $book->resources;
                
            $resourceIds = array_values(array_diff($resources['resource_ids'], [$resourceId]));

            $book->resources = json_encode([
                'book_id' => $book->id,
                'resource_ids' => $resourceIds
            ]);
            $book->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa tài liệu thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting resource: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa tài liệu: ' . $e->getMessage()
            ], 500);
        }
    }
}

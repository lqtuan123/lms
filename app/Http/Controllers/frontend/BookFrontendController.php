<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Tag;
use App\Modules\Book\Models\Book;
use App\Modules\Book\Models\BookType;
use App\Modules\Resource\Models\Resource;
use App\Providers\RecentBooksService; // Sửa namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookFrontendController extends Controller
{
    protected $recentBooksService;

    public function __construct(RecentBooksService $recentBooksService)
    {
        $this->recentBooksService = $recentBooksService;
    }

    public function index(Request $request)
    {
        try {
            $query = Book::query();

            // Xử lý tìm kiếm cơ bản
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            // Lấy danh sách sách với relations
            $books = $query->with(['user', 'bookType'])
                ->paginate(12);

            // Lấy danh mục sách active
            $booktypes = BookType::withCount('books')
                ->where('status', 'active')
                ->get();

            // Lấy sách nổi bật
            $featuredBooks = Book::with('user')
                ->limit(5)
                ->get();

            // Lấy sách đề cử (top views)
            $recommendedBooks = Book::where('views', '>', 0)
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();

            // Lấy sách vừa đọc
            $recentBooks = $this->recentBooksService->getRecentBooks();

            return view('frontend.book.index', compact(
                'books',
                'booktypes',
                'featuredBooks',
                'recommendedBooks',
                'recentBooks'
            ));
        } catch (\Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function show($slug)
    {
        try {
            // Tìm sách theo slug với relations
            $book = Book::with(['user', 'bookType'])
                ->where('slug', $slug)
                ->firstOrFail();

            // Tăng lượt xem trong transaction
            DB::transaction(function () use ($book) {
                $book->increment('views');
            });

            // Thêm vào sách vừa đọc
            $this->recentBooksService->addBook($book->id);

            // Lấy danh mục sách active
            $booktypes = BookType::withCount('books')
                ->where('status', 'active')
                ->get();

            // Lấy sách nổi bật
            $featuredBooks = Book::with('user')
                ->limit(5)
                ->get();

            // Lấy sách liên quan
            $relatedBooks = Book::where('id', '!=', $book->id)
                ->limit(4)
                ->get();

            // Lấy comments
            $comments = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive(
                $book->id,
                'book'
            );

            // Lấy resources
            $resourceIds = json_decode($book->resources, true)['resource_ids'] ?? [];
            $resources = Resource::whereIn('id', $resourceIds)->get();

            // Lấy tags
            $tags = DB::table('tag_books')
                ->where('book_id', $book->id)
                ->pluck('tag_id');
            $tagNames = Tag::whereIn('id', $tags)->pluck('title');

            return view('frontend.book.show', compact(
                'book',
                'resources',
                'tagNames',
                'comments',
                'booktypes',
                'featuredBooks',
                'relatedBooks'
            ));
        } catch (\Exception $e) {
            Log::error('Error in show method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function booksByType($slug)
    {
        try {
            $bookType = BookType::where('slug', $slug)->firstOrFail();

            $books = Book::where('book_type_id', $bookType->id)
                ->paginate(10);

            $booktypes = BookType::withCount('books')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->limit(5)
                ->get();

            return view('frontend.book.by-type', compact(
                'bookType',
                'books',
                'booktypes',
                'featuredBooks'
            ));
        } catch (\Exception $e) {
            Log::error('Error in booksByType method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function advancedSearch(Request $request)
    {
        // dd(request());

        try {
            $tags = Tag::orderBy('title')->get();
            $query = Book::query();
            $booktypes = BookType::withCount('books')
                ->where('status', 'active')
                ->get();

            // Tìm theo tiêu đề
            if ($request->filled('book_title')) {
                $query->where('title', 'like', '%' . $request->book_title . '%');
            }

            // Tìm theo loại sách
            if ($request->filled('book_type_id')) {
                $query->where('book_type_id', $request->book_type_id);
            }

            // Tìm theo mô tả
            if ($request->filled('summary')) {
                $query->where('summary', 'like', '%' . $request->summary . '%');
            }

            // Tìm theo tags
            if ($request->filled('tags')) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('tags.id', $request->tags);
                });
            }

            $books = $query->with('tags')->paginate(12);

            return view('frontend.book.advanced-search', compact('books', 'tags','booktypes'));
        } catch (\Exception $e) {
            Log::error('Error in advancedSearch method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tìm kiếm.');
        }
    }

    public function Search(Request $request)
    {
        try {
            $query = Book::query();
            $booktypes = BookType::withCount('books')
                ->where('status', 'active')
                ->get();
            $featuredBooks = Book::with('user')
                ->limit(5)
                ->get();
            // Tìm theo tiêu đề
            if ($request->filled('book_title')) {
                $query->where('title', 'like', '%' . $request->book_title . '%');
            }

            // Lấy dữ liệu từ query
            $books = $query->paginate(10); // Hoặc ->get() nếu không cần phân trang

            return view('frontend.book.search', compact('books', 'booktypes','featuredBooks'));
        } catch (\Exception $e) {
            Log::error('Error in Search method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tìm kiếm.');
        }
    }

    public function saveBookComment(Request $request)
    {
        try {
            $request->merge(['item_code' => 'book']);
            $commentController = new \App\Modules\Tuongtac\Controllers\TCommentController();
            return $commentController->saveComment($request);
        } catch (\Exception $e) {
            Log::error('Error in saveBookComment method: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi lưu bình luận.'], 500);
        }
    }
}

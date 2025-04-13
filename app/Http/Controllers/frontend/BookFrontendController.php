<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Tag;
use App\Models\User;
use App\Modules\Book\Models\Book;
use App\Modules\Book\Models\BookType;
use App\Modules\Resource\Models\Resource;
use App\Modules\Tuongtac\Models\TUserpage;
use App\Providers\RecentBooksService; // Sửa namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Modules\Tuongtac\Models\TRecommend;
use App\Modules\Tuongtac\Models\TVoteItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookFrontendController extends Controller
{
    protected $recentBooksService;

    public function __construct(RecentBooksService $recentBooksService)
    {
        $this->recentBooksService = $recentBooksService;
    }

    public function index()
    {
        try {
            // This data is now fetched directly in the book.blade.php layout
            $books = Book::with(['user', 'bookType'])
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('id', 'desc')
                ->paginate(10);

            // Lấy danh sách ID sách được bookmark bởi người dùng hiện tại
            $bookmarkedIds = [];
            if ($user = Auth::user()) {
                $bookmarkedIds = \App\Modules\Tuongtac\Models\TRecommend::where('user_id', $user->id)
                    ->where('item_code', 'book')
                    ->pluck('item_id')
                    ->toArray();
            }

            // Thêm thông tin vote và bookmark cho mỗi sách
            foreach ($books as $book) {
                $voteItem = TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                $book->vote_count = $voteItem?->count ?? 0;
                $book->vote_point = $voteItem?->point ?? 0;
                $book->vote_average = $voteItem?->point ?? 0;
                $book->is_bookmarked = in_array($book->id, $bookmarkedIds);
            }

            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            $recommendedBooks = Book::where('views', '>', 0)
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();

            $recentBooks = $this->recentBooksService->getRecentBooks();

            return view('frontend.book.index', compact(
                'books',
                'booktypes',
                'featuredBooks',
                'recentBooks',
                'recommendedBooks',
                'bookmarkedIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in index method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function create()
    {
        $tags = Tag::where('status', 'active')->orderBy('title', 'ASC')->get();
        $bookTypes = BookType::all();

        return view('frontend.book.userbook.create', compact('tags', 'bookTypes'));
    }

    // Xử lý lưu sách từ frontend
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|string', // Nhận URL từ Dropzone
            'summary' => 'nullable|string|max:1000',
            'content' => 'nullable|string',
            'document' => 'required|array',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx|max:204800',
            'status' => 'required|in:active,inactive',
            'tag_ids' => 'nullable|array',
            'book_type_id' => 'required|exists:book_types,id',
        ]);

        $userId = Auth::id();
        $resourceIds = [];

        // Xử lý tài liệu (document)
        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $resource = Resource::createResource($request, $file, 'Book');
                if ($resource) {
                    $resourceIds[] = $resource->id;
                }
            }
        }

        // Tạo slug duy nhất
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        while (Book::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Xử lý ảnh
        $photo = null;
        if ($request->photo) {
            $photo = $request->photo;
            // Nếu đường dẫn là URL đầy đủ, cắt bỏ domain
            if (filter_var($photo, FILTER_VALIDATE_URL)) {
                $photo = str_replace(url('/'), '', $photo);
            }
            
            // Đảm bảo đường dẫn bắt đầu bằng /
            if (!Str::startsWith($photo, '/')) {
                $photo = '/' . $photo;
            }
        }

        // Tạo sách
        $book = Book::create([
            'title' => $request->title,
            'slug' => $slug,
            'photo' => $photo,
            'summary' => $request->summary,
            'content' => $request->content,
            'status' => $request->status,
            'user_id' => $userId,
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

        // Gán tags nếu có    
        if ($request->tag_ids) {
            (new \App\Http\Controllers\TagController())->store_book_tag($book->id, $request->tag_ids);
        }

        return redirect()->route('front.book.index')->with('success', 'Sách đã được tạo thành công!');
    }

    public function show($slug)
    {
        try {
            // Tìm sách theo slug với relations
            $book = Book::with(['user', 'bookType'])
                ->where('slug', $slug)
                ->where('status', 'active')
                ->where('block', 'no')
                ->firstOrFail();

            // Tăng lượt xem trong transaction
            DB::transaction(function () use ($book) {
                $book->increment('views');
                if (Auth::check()) {
                    TUserpage::add_points(Auth::id(), 1);
                }
            });

            // Thêm vào sách vừa đọc
            $this->recentBooksService->addBook($book->id);

            // Lấy danh mục sách active
            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            // Lấy sách nổi bật
            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            // Lấy sách liên quan
            $relatedBooks = Book::where('id', '!=', $book->id)
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(4)
                ->get();

            // Lấy comments
            $comments = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive(
                $book->id,
                'book'
            );

            // Lấy resources
            $resourceIds = [];
            
            if (!empty($book->resources)) {
                // Handle both string and array formats
                if (is_string($book->resources)) {
                    $resourcesData = json_decode($book->resources, true);
                    $resourceIds = $resourcesData['resource_ids'] ?? [];
                } else if (is_array($book->resources) && isset($book->resources['resource_ids'])) {
                    $resourceIds = $book->resources['resource_ids'];
                }
            }
            
            $resources = Resource::whereIn('id', $resourceIds)->get();

            // Lấy tags
            $tags = DB::table('tag_books')
                ->where('book_id', $book->id)
                ->pluck('tag_id');
            $tagNames = Tag::whereIn('id', $tags)->pluck('title');
            
            // Lấy thông tin đánh giá sao
            $voteItem = TVoteItem::where('item_id', $book->id)
                ->where('item_code', 'book')
                ->first();

            $book->vote_count = $voteItem?->count ?? 0;
            $book->vote_average = $voteItem?->point ?? 0;

            // Lấy thông tin đánh giá của người dùng hiện tại
            $userVote = null;
            if (Auth::check() && $voteItem) {
                $votes = is_string($voteItem->votes) ? json_decode($voteItem->votes, true) : $voteItem->votes;
                $userId = Auth::id();
                $book->user_vote = $votes[$userId] ?? null;
            }

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
                ->where('status', 'active')
                ->where('block', 'no')
                ->paginate(10);

            // Lấy danh sách ID sách được bookmark bởi người dùng hiện tại
            $bookmarkedIds = [];
            if ($user = Auth::user()) {
                $bookmarkedIds = \App\Modules\Tuongtac\Models\TRecommend::where('user_id', $user->id)
                    ->where('item_code', 'book')
                    ->pluck('item_id')
                    ->toArray();
            }

            // Thêm thông tin vote và bookmark cho mỗi sách
            foreach ($books as $book) {
                $voteItem = TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                $book->vote_count = $voteItem?->count ?? 0;
                $book->vote_point = $voteItem?->point ?? 0;
                $book->vote_average = $voteItem?->point ?? 0;
                $book->is_bookmarked = in_array($book->id, $bookmarkedIds);
            }

            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            $recommendedBooks = Book::where('views', '>', 0)
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();
            $recentBooks = $this->recentBooksService->getRecentBooks();

            return view('frontend.book.index', compact(
                'bookType',
                'books',
                'booktypes',
                'featuredBooks',
                'recentBooks',
                'recommendedBooks',
                'bookmarkedIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in booksByType method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function booksByMultiTypes(Request $request)
    {
        try {
            $bookTypeIds = $request->input('book_types', []);

            $books = Book::when(!empty($bookTypeIds), function ($query) use ($bookTypeIds) {
                $query->whereIn('book_type_id', $bookTypeIds);
            })
                ->where('status', 'active')
                ->where('block', 'no')
                ->paginate(10);

            // Lấy danh sách ID sách được bookmark bởi người dùng hiện tại
            $bookmarkedIds = [];
            if ($user = Auth::user()) {
                $bookmarkedIds = \App\Modules\Tuongtac\Models\TRecommend::where('user_id', $user->id)
                    ->where('item_code', 'book')
                    ->pluck('item_id')
                    ->toArray();
            }

            // Thêm thông tin vote và bookmark cho mỗi sách
            foreach ($books as $book) {
                $voteItem = TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                $book->vote_count = $voteItem?->count ?? 0;
                $book->vote_point = $voteItem?->point ?? 0;
                $book->vote_average = $voteItem?->point ?? 0;
                $book->is_bookmarked = in_array($book->id, $bookmarkedIds);
            }

            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            $recommendedBooks = Book::where('views', '>', 0)
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();

            $recentBooks = $this->recentBooksService->getRecentBooks();

            return view('frontend.book.index', compact(
                'books',
                'booktypes',
                'featuredBooks',
                'recentBooks',
                'recommendedBooks',
                'bookmarkedIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in booksByMultiTypes method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang.');
        }
    }

    public function showRecentBooks()
    {
        try {
            // Lấy danh sách sách vừa đọc
            $recentBookIds = $this->recentBooksService->getAllRecentBooks(20)->pluck('id')->toArray();

            if (empty($recentBookIds)) {
                // Nếu không có sách vừa đọc, trả về collection rỗng dưới dạng paginator
                $books = Book::where('id', 0)->paginate(12);
            } else {
                // Lấy thông tin đầy đủ của sách từ database
                $books = Book::whereIn('id', $recentBookIds)
                    ->where('status', 'active')
                    ->where('block', 'no')
                    ->paginate(12);
            }

            // Lấy danh sách ID sách được bookmark bởi người dùng hiện tại
            $bookmarkedIds = [];
            if ($user = Auth::user()) {
                $bookmarkedIds = \App\Modules\Tuongtac\Models\TRecommend::where('user_id', $user->id)
                    ->where('item_code', 'book')
                    ->pluck('item_id')
                    ->toArray();
            }

            // Thêm thông tin vote và bookmark cho mỗi sách
            foreach ($books as $book) {
                $voteItem = TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                $book->vote_count = $voteItem?->count ?? 0;
                $book->vote_point = $voteItem?->point ?? 0;
                $book->vote_average = $voteItem?->point ?? 0;
                $book->is_bookmarked = in_array($book->id, $bookmarkedIds);
            }

            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            $recommendedBooks = Book::where('views', '>', 0)
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();

            $recentBooks = $this->recentBooksService->getAllRecentBooks(5);

            return view('frontend.book.index', compact(
                'booktypes',
                'featuredBooks',
                'books',
                'recentBooks',
                'recommendedBooks',
                'bookmarkedIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in showRecentBooks method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang sách vừa đọc.');
        }
    }

    public function advancedSearch(Request $request)
    {
        // dd(request());

        try {
            $tags = Tag::orderBy('title')->get();
            $query = Book::query()
                ->where('status', 'active')
                ->where('block', 'no');
            $booktypes = BookType::withCount('activeBooks')
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

            return view('frontend.book.advanced-search', compact('books', 'tags', 'booktypes'));
        } catch (\Exception $e) {
            Log::error('Error in advancedSearch method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tìm kiếm.');
        }
    }

    public function Search(Request $request)
    {
        try {
            $query = Book::query()
                ->where('status', 'active')
                ->where('block', 'no');

            // Tìm theo tiêu đề
            if ($request->filled('book_title')) {
                $query->where('title', 'like', '%' . $request->book_title . '%');
            }

            // Lấy danh sách ID sách được bookmark bởi người dùng hiện tại
            $bookmarkedIds = [];
            if ($user = Auth::user()) {
                $bookmarkedIds = \App\Modules\Tuongtac\Models\TRecommend::where('user_id', $user->id)
                    ->where('item_code', 'book')
                    ->pluck('item_id')
                    ->toArray();
            }

            // Lấy dữ liệu từ query
            $books = $query->paginate(12);

            // Thêm thông tin vote và bookmark cho mỗi sách
            foreach ($books as $book) {
                $voteItem = TVoteItem::where('item_id', $book->id)->where('item_code', 'book')->first();
                $book->vote_count = $voteItem?->count ?? 0;
                $book->vote_point = $voteItem?->point ?? 0;
                $book->vote_average = $voteItem?->point ?? 0;
                $book->is_bookmarked = in_array($book->id, $bookmarkedIds);
            }

            $booktypes = BookType::withCount('activeBooks')
                ->where('status', 'active')
                ->get();

            $featuredBooks = Book::with('user')
                ->where('status', 'active')
                ->where('block', 'no')
                ->limit(5)
                ->get();

            $recommendedBooks = Book::where('views', '>', 0)
                ->where('status', 'active')
                ->where('block', 'no')
                ->orderBy('views', 'desc')
                ->limit(6)
                ->get();

            $recentBooks = $this->recentBooksService->getRecentBooks();

            return view('frontend.book.index', compact(
                'books',
                'booktypes',
                'featuredBooks',
                'recentBooks',
                'recommendedBooks',
                'bookmarkedIds'
            ));
        } catch (\Exception $e) {
            Log::error('Error in Search method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tìm kiếm.');
        }
    }

    public function saveBookComment(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['error' => 'Bạn cần đăng nhập để bình luận'], 401);
            }

            $request->merge(['item_code' => 'book']);
            $commentController = new \App\Modules\Tuongtac\Controllers\TCommentController();
            TUserpage::add_points(Auth::id(), 1);
            return $commentController->saveComment($request);
        } catch (\Exception $e) {
            Log::error('Error in saveBookComment method: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi lưu bình luận.'], 500);
        }
    }

    public function bookMark(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'msg' => 'Bạn phải đăng nhập']);
        }

        $request->validate([
            'item_id' => 'required|integer',
            'item_code' => 'required|string',
        ]);

        $itemId = $request->item_id;
        $itemCode = $request->item_code;

        // Toggle bookmark
        $isBookmarked = TRecommend::toggleBookmark($itemId, $itemCode);

        return response()->json([
            'success' => true,
            'isBookmarked' => $isBookmarked
        ]);
    }

    public static function vote($item_id, $item_code)
    {
        $data['item_id'] = $item_id;
        $data['item_code'] = $item_code;

        $data['voteRecord'] = DB::table('t_vote_items')->where('item_id', $item_id)->first();

        $html = view('frontend.book.show', $data)->render();
        return $html;
    }
}

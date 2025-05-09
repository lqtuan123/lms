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
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Modules\Tuongtac\Controllers\TCommentController;
use App\Modules\Tuongtac\Models\TComment;

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
            // Xác định loại sắp xếp từ request
            $sort = request('sort', 'latest');

            // This data is now fetched directly in the book.blade.php layout
            $query = Book::with(['user', 'bookType'])
                ->where('status', 'active')
                ->where('block', 'no');

            // Áp dụng sắp xếp theo tùy chọn
            switch ($sort) {
                case 'views':
                    $query->orderBy('views', 'desc');
                    break;
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('id', 'desc');
                    break;
            }

            $books = $query->paginate(30);

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
                $book->vote_count = $book->rating_count ?? 0;
                $book->vote_point = $book->average_rating ?? 0;
                $book->vote_average = $book->average_rating ?? 0;
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
            // Chỉ thêm dấu / nếu không phải là URL đầy đủ
            if (!filter_var($photo, FILTER_VALIDATE_URL) && !Str::startsWith($photo, '/')) {
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

        // Cộng điểm khi đăng sách mới
        if (Auth::check()) {
            Auth::user()->addPoint('upload_book', $book->id, 'App\Modules\Book\Models\Book');
        }

        // Gán tags nếu có    
        if ($request->tag_ids) {
            (new \App\Http\Controllers\TagController())->store_book_tag($book->id, $request->tag_ids);
        }

        return redirect()->route('front.profile')->with('success', 'Sách đã được tạo thành công!')->withFragment('books');
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
            $book->comment_count = TComment::where('item_id', $book->id)
                ->where('item_code', 'book')
                ->where('status', 'active')
                ->count();

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
            $voteItem = Rating::where('book_id', $book->id)->first();

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
            // Xác định loại sắp xếp từ request
            $sort = request('sort', 'latest');

            $bookType = BookType::where('slug', $slug)->firstOrFail();

            $query = Book::where('book_type_id', $bookType->id)
                ->where('status', 'active')
                ->where('block', 'no');

            // Áp dụng sắp xếp theo tùy chọn
            switch ($sort) {
                case 'views':
                    $query->orderBy('views', 'desc');
                    break;
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'title_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'latest':
                default:
                    $query->orderBy('id', 'desc');
                    break;
            }

            $books = $query->paginate(10);

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
                $book->vote_count = $book->rating_count ?? 0;
                $book->vote_point = $book->average_rating ?? 0;
                $book->vote_average = $book->average_rating ?? 0;
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
                $book->vote_count = $book->rating_count ?? 0;
                $book->vote_point = $book->average_rating ?? 0;
                $book->vote_average = $book->average_rating ?? 0;
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
                $book->vote_count = $book->rating_count ?? 0;
                $book->vote_point = $book->average_rating ?? 0;
                $book->vote_average = $book->average_rating ?? 0;
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

            return view('frontend.book.index', compact('books', 'tags', 'booktypes'));
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
                $book->vote_count = $book->rating_count ?? 0;
                $book->vote_point = $book->average_rating ?? 0;
                $book->vote_average = $book->average_rating ?? 0;
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

            // Xử lý mentions trong nội dung comment
            $content = $request->content;
            $pattern = '/@\[(.*?)\]\((.*?)\)/';

            // Thay thế mentions bằng HTML
            $content = preg_replace_callback($pattern, function ($matches) {
                $bookTitle = $matches[1];
                $bookUrl = $matches[2];
                return '<a href="' . $bookUrl . '" class="book-mention">@' . $bookTitle . '</a>';
            }, $content);

            // Cập nhật nội dung đã xử lý vào request
            $request->merge(['content' => $content]);

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
        
        $data['voteRecord'] = Rating::where('book_id', $item_id)->first();
        
        $html = view('frontend.book.show', $data)->render();
        return $html;
    }

    // Thêm phương thức đọc sách mới
    public function readBook($id)
    {
        try {
            // Tìm sách theo id
            $book = Book::with(['user', 'bookType'])
                ->where('id', $id)
                ->where('status', 'active')
                ->where('block', 'no')
                ->firstOrFail();

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

            // Thêm vào sách vừa đọc
            $this->recentBooksService->addBook($book->id);

            // Tăng lượt xem một lần khi bắt đầu đọc
            DB::transaction(function () use ($book) {
                $book->increment('views');
            });

            // Khởi tạo session đọc sách nếu chưa có
            if (!session()->has('reading_session_' . $book->id)) {
                session([
                    'reading_session_' . $book->id => [
                        'start_time' => now(),
                        'last_active' => now(),
                        'total_minutes' => 0,
                        'points_earned' => 0
                    ]
                ]);
            }

            return view('frontend.book.reader', compact('book', 'resources'));
        } catch (\Exception $e) {
            Log::error('Error in readBook method: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang đọc sách.');
        }
    }

    /**
     * API endpoint để cập nhật thời gian đọc sách
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReadingTime(Request $request)
    {
        $request->validate([
            'book_id' => 'required|integer',
            'active' => 'required|boolean'
        ]);

        $bookId = $request->book_id;
        $isActive = $request->active;

        // Người dùng phải đăng nhập để tính điểm
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để tích điểm đọc sách.']);
        }

        // Kiểm tra phiên đọc sách
        $sessionKey = 'reading_session_' . $bookId;
        if (!session()->has($sessionKey)) {
            return response()->json(['success' => false, 'message' => 'Phiên đọc sách không tồn tại.']);
        }

        $session = session($sessionKey);
        $now = now();
        $lastActive = \Carbon\Carbon::parse($session['last_active']);

        // Tính thời gian giữa lần cập nhật trước và bây giờ (tính bằng phút)
        // Chỉ tính thời gian nếu < 2 phút (để tránh trường hợp người dùng rời đi và quay lại)
        $minutesPassed = 0;
        if ($isActive && $lastActive->diffInMinutes($now) < 2) {
            $minutesPassed = $lastActive->diffInSeconds($now) / 60;
        }

        // Cập nhật tổng thời gian đọc
        $totalMinutes = $session['total_minutes'] + $minutesPassed;
        $pointsEarned = $session['points_earned'];

        // Tính số điểm mới
        $pointsToEarn = floor($totalMinutes / 5) - $pointsEarned;

        // Nếu có điểm mới, cộng điểm cho người dùng
        if ($pointsToEarn > 0) {
            // Cộng điểm cho người dùng
            for ($i = 0; $i < $pointsToEarn; $i++) {
                Auth::user()->addPoint('read_book', $bookId, 'App\Modules\Book\Models\Book', 'Đọc sách trong ' . ($pointsEarned + $i + 1) * 5 . ' phút');
            }
            $pointsEarned += $pointsToEarn;
        }

        // Cập nhật session
        session([
            $sessionKey => [
                'start_time' => $session['start_time'],
                'last_active' => $now,
                'total_minutes' => $totalMinutes,
                'points_earned' => $pointsEarned
            ]
        ]);

        return response()->json([
            'success' => true,
            'total_minutes' => round($totalMinutes, 2),
            'points_earned' => $pointsEarned
        ]);
    }

    /**
     * Kết thúc phiên đọc sách và tính điểm cuối cùng
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function finishReading(Request $request)
    {
        $request->validate([
            'book_id' => 'required|integer'
        ]);

        $bookId = $request->book_id;

        // Người dùng phải đăng nhập để tính điểm
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để tích điểm đọc sách.']);
        }

        // Kiểm tra phiên đọc sách
        $sessionKey = 'reading_session_' . $bookId;
        if (!session()->has($sessionKey)) {
            return response()->json(['success' => false, 'message' => 'Phiên đọc sách không tồn tại.']);
        }

        // Xóa phiên đọc sách
        $session = session($sessionKey);
        session()->forget($sessionKey);

        return response()->json([
            'success' => true,
            'total_minutes' => round($session['total_minutes'], 2),
            'points_earned' => $session['points_earned']
        ]);
    }

    /**
     * Bắt đầu phiên đọc sách mới
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function startReading(Request $request)
    {
        $request->validate([
            'book_id' => 'required|integer'
        ]);

        $bookId = $request->book_id;

        // Tạo ID phiên duy nhất
        $sessionId = uniqid('read_', true);

        // Tạo hoặc reset phiên đọc sách
        $sessionKey = 'reading_session_' . $bookId;

        // Kiểm tra nếu phiên đã tồn tại, reset nó
        if (session()->has($sessionKey)) {
            session()->forget($sessionKey);
        }

        // Khởi tạo phiên mới
        session([
            $sessionKey => [
                'session_id' => $sessionId,
                'start_time' => now(),
                'last_active' => now(),
                'total_minutes' => 0,
                'points_earned' => 0
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã bắt đầu phiên đọc sách mới',
            'session_id' => $sessionId
        ]);
    }

    public function searchForMention(Request $request)
    {
        try {
            $query = $request->get('query');
            Log::info('Searching for books with query: ' . $query);

            if (empty($query)) {
                return response()->json([]);
            }

            $books = \App\Modules\Book\Models\Book::where('title', 'like', '%' . $query . '%')
                ->where('status', 'active')
                ->where('block', 'no')
                ->select('id', 'title', 'slug')
                ->limit(5)
                ->get()
                ->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'slug' => $book->slug,
                        'url' => route('front.book.show', $book->slug)
                    ];
                });

            Log::info('Found books: ' . $books->count());
            return response()->json($books);
        } catch (\Exception $e) {
            Log::error('Error in searchForMention method: ' . $e->getMessage());
            // Trả về mảng rỗng thay vì lỗi 500 để tránh lỗi 404 trong console
            return response()->json([]);
        }
    }

    /**
     * API endpoint để lấy danh sách tài liệu của sách
     * 
     * @param int $id ID của sách
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookResources($id)
    {
        try {
            // Tìm sách theo ID
            $book = Book::findOrFail($id);

            // Lấy danh sách ID tài nguyên
            $resourceIds = [];

            if (!empty($book->resources)) {
                if (is_string($book->resources)) {
                    $resourcesData = json_decode($book->resources, true);
                    $resourceIds = $resourcesData['resource_ids'] ?? [];
                } else if (is_array($book->resources) && isset($book->resources['resource_ids'])) {
                    $resourceIds = $book->resources['resource_ids'];
                }
            }

            // Lấy thông tin chi tiết của các tài nguyên
            $resources = \App\Modules\Resource\Models\Resource::whereIn('id', $resourceIds)->get();

            // Bổ sung thông tin link tải xuống trực tiếp
            foreach ($resources as $resource) {
                // Kiểm tra loại link
                if ($resource->link_code !== 'youtube') {
                    // Xác định loại icon dựa trên định dạng file
                    $resource->icon_class = $this->getResourceIconClass($resource->file_type);

                    // Đối với URL, thêm thông tin xem có thể tải xuống không
                    if ($resource->link_code === 'url') {
                        $resource->is_downloadable = !empty($resource->file_name) &&
                            $resource->file_name != 'unknown_file' &&
                            $resource->file_name != 'file.unknown';
                    } else {
                        $resource->is_downloadable = true;
                    }
                    
                    // Tạo đường dẫn tải xuống
                    $resource->download_url = \App\Modules\Resource\Models\Resource::createDownloadLink($resource);
                }
            }

            return response()->json([
                'success' => true,
                'resources' => $resources
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getBookResources method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách tài liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy lớp CSS icon cho tài nguyên dựa trên loại file
     * 
     * @param string $fileType MIME type của file
     * @return string Lớp CSS cho icon
     */
    private function getResourceIconClass($fileType)
    {
        if (empty($fileType)) {
            return 'fas fa-file';
        }

        $fileType = strtolower($fileType);

        if (strpos($fileType, 'pdf') !== false) {
            return 'fas fa-file-pdf';
        } elseif (strpos($fileType, 'word') !== false || strpos($fileType, 'doc') !== false) {
            return 'fas fa-file-word';
        } elseif (strpos($fileType, 'spreadsheet') !== false || strpos($fileType, 'excel') !== false || strpos($fileType, 'xls') !== false) {
            return 'fas fa-file-excel';
        } elseif (strpos($fileType, 'presentation') !== false || strpos($fileType, 'powerpoint') !== false || strpos($fileType, 'ppt') !== false) {
            return 'fas fa-file-powerpoint';
        } elseif (strpos($fileType, 'image') !== false) {
            return 'fas fa-file-image';
        } elseif (strpos($fileType, 'audio') !== false || strpos($fileType, 'mp3') !== false) {
            return 'fas fa-file-audio';
        } elseif (strpos($fileType, 'video') !== false || strpos($fileType, 'mp4') !== false) {
            return 'fas fa-file-video';
        } elseif (strpos($fileType, 'zip') !== false || strpos($fileType, 'archive') !== false || strpos($fileType, 'compressed') !== false) {
            return 'fas fa-file-archive';
        } elseif (strpos($fileType, 'text') !== false || strpos($fileType, 'txt') !== false) {
            return 'fas fa-file-alt';
        } elseif (strpos($fileType, 'code') !== false || strpos($fileType, 'html') !== false || strpos($fileType, 'xml') !== false || strpos($fileType, 'json') !== false) {
            return 'fas fa-file-code';
        }

        return 'fas fa-file';
    }
}

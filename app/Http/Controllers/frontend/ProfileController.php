<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SettingDetail;
use App\Models\Links;
use App\Modules\Book\Models\Book;
use App\Modules\Tuongtac\Models\TMotionItem;
use App\Modules\Tuongtac\Models\TRecommend;
use App\Models\UserPrivacySetting;

class ProfileController extends Controller
{
    protected $front_view = 'frontend';

    public function __construct()
    {
        $this->middleware('auth')->except(['viewUserProfile']);
    }

    private function loadCommonData($title)
    {
        return [
            'detail' => SettingDetail::find(1),
            'pagetitle' => $title,
            'profile' => Auth::user()
        ];
    }

    public function order()
    {
        $data = $this->loadCommonData("Thông tin tài khoản");
        return view("{$this->front_view}.profile.view", $data);
    }

    public function viewWishlist()
    {
        $data = $this->loadCommonData("Sản phẩm yêu thích");
        $data['links'] = [(new Links(['title' => 'Danh sách sản phẩm yêu thích', 'url' => '#']))];
        return view("{$this->front_view}.profile.wishlist", $data);
    }

    public function viewDashboard()
    {
        $data = $this->loadCommonData("Thông tin tài khoản");
        $data['links'] = [
            (new Links(['title' => 'Thông tin tài khoản', 'url' => '#']))
        ];
    
        $userId = Auth::id();
        
        // Đặt người dùng hiện tại là chủ tài khoản
        $data['isOwner'] = true;
    
        // Lấy cài đặt riêng tư của người dùng
        $data['privacySettings'] = UserPrivacySetting::where('user_id', $userId)->first();
    
        // Thống kê chung
        $data['bookCount'] = Book::where('user_id', $userId)->count();
        // Tổng lượt xem sách
        $data['totalBookViews'] = DB::table('books')
            ->where('user_id', $userId)
            ->sum('views');
        $data['commentCount'] = DB::table('t_comments')
            ->where('user_id', $userId)
            ->count();
        $data['reactions_count'] = DB::table('t_motion_items')
            ->where('item_id', $userId)
            ->where('item_code', 'tblog')
            ->count();
        $data['likeCount'] = DB::table('t_recommends')
            ->where('user_id', $userId)
            ->count();
        $data['postCount'] = DB::table('t_blogs')
            ->where('user_id', $userId)
            ->count();
        // Tổng lượt xem bài viết
        $data['totalBlogViews'] = DB::table('t_blogs')
            ->where('user_id', $userId)
            ->sum('hit');
    
        // Sách của người dùng
        $data['books'] = Book::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(8);
    
        // Sách yêu thích
        $favoriteBookIds = DB::table('t_recommends')
            ->where('user_id', $userId)
            ->where('item_code', 'book')
            ->pluck('item_id')
            ->toArray();
        $data['favoriteBooks'] = Book::whereIn('id', $favoriteBookIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    
        // Bài viết của người dùng
        $userPosts = DB::table('t_blogs as b')
            ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
            ->where('b.user_id', $userId)
            ->select(
                'b.*',
                'u.full_name as author_name',
                'u.photo as author_photo',
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_comments WHERE item_id = b.id AND item_code = "tblog"), 0) as comment_count'),
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_recommends WHERE item_id = b.id AND item_code = "tblog"), 0) as likes_count'),
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_motion_items WHERE item_id = b.id AND item_code = "tblog"), 0) as reactions_count')
            )
            ->orderBy('b.created_at', 'desc')
            ->limit(3)
            ->get();
    
        // Gắn thêm tags, trạng thái like, author, URL và likes_motion_count
        foreach ($userPosts as $post) {
            // Tags
            $post->tags = DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $post->id)
                ->where('t_tag_items.item_code', 'tblog')
                ->select('t_tags.*')
                ->get();
    
            // Người dùng đã like?
            $post->user_has_liked = DB::table('t_recommends')
                ->where('user_id', $userId)
                ->where('item_id', $post->id)
                ->where('item_code', 'tblog')
                ->exists();
    
            // URL profile tác giả
            $post->user_url = route('front.profile');
    
            // Thông tin tác giả
            $post->author = (object)[
                'id' => $post->user_id,
                'full_name' => $post->author_name,
                'photo' => $post->author_photo
            ];
    
            // Tổng reactions từ TMotionItem
            $motionItem = TMotionItem::where('item_id', $post->id)
                ->where('item_code', 'tblog')
                ->first();
            $post->likes_motion_count = $motionItem
                ? $motionItem->getTotalReactionsCount()
                : 0;
                
            // Tính tổng số like (cả recommends và motion_items)
            $post->total_likes = $post->likes_count + $post->likes_motion_count;
                
            // Kiểm tra xem người dùng đã bookmark bài viết chưa
            $post->is_bookmarked = TRecommend::hasBookmarked($post->id, 'tblog');
        }
        $data['userPosts'] = $userPosts;
    
        // Bài viết đã like
        $likedPostIds = DB::table('t_recommends')
            ->where('user_id', $userId)
            ->where('item_code', 'tblog')
            ->pluck('item_id')
            ->toArray();
    
        $likedPosts = DB::table('t_blogs as b')
            ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
            ->whereIn('b.id', $likedPostIds)
            ->select(
                'b.*',
                'u.full_name as author_name',
                'u.photo as author_photo',
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_comments WHERE item_id = b.id AND item_code = "tblog"), 0) as comment_count'),
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_recommends WHERE item_id = b.id AND item_code = "tblog"), 0) as likes_count'),
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_motion_items WHERE item_id = b.id AND item_code = "tblog"), 0) as reactions_count')
            )
            ->orderBy('b.created_at', 'desc')
            ->limit(3)
            ->get();
    
        // Gắn thêm tags, author và likes_motion_count cho các bài đã like
        foreach ($likedPosts as $post) {
            $post->tags = DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $post->id)
                ->where('t_tag_items.item_code', 'tblog')
                ->select('t_tags.*')
                ->get();
    
            $post->user_has_liked = true;  // Người dùng đã like
    
            $post->author = (object)[
                'id' => $post->user_id,
                'full_name' => $post->author_name,
                'photo' => $post->author_photo
            ];
    
            $motionItem = TMotionItem::where('item_id', $post->id)
                ->where('item_code', 'tblog')
                ->first();
            $post->likes_motion_count = $motionItem
                ? $motionItem->getTotalReactionsCount()
                : 0;
                
            // Kiểm tra xem người dùng đã bookmark bài viết chưa
            $post->is_bookmarked = TRecommend::hasBookmarked($post->id, 'tblog');
        }
        $data['likedPosts'] = $likedPosts;
    
        // Số lượng yêu thích
        $data['likeBookCount'] = count($favoriteBookIds);
        $data['likePostCount'] = count($likedPostIds);
    
        // Hoạt động gần đây & bài viết yêu thích
        $data['recentActivities'] = $this->getUserRecentActivities($userId);
        
        // Lấy bài viết yêu thích thay vì bài viết nổi bật
        $data['favoritePosts'] = DB::table('t_blogs as b')
            ->select('b.*', 't_recommends.created_at as recommend_date')
            ->join('t_recommends', function ($join) use ($userId) {
                $join->on('b.id', '=', 't_recommends.item_id')
                    ->where('t_recommends.item_code', 'tblog')
                    ->where('t_recommends.user_id', $userId);
            })
            ->where('b.status', 1) // Chỉ lấy bài viết có status = 1
            ->orderBy('recommend_date', 'desc') // Sắp xếp theo thời gian yêu thích mới nhất
            ->limit(5) // Giới hạn 5 bài viết
            ->get();
    
        return view("{$this->front_view}.profile.profile", $data);
    }
    

    private function getUserRecentActivities($userId)
    {
        // Lấy hoạt động gần đây từ các bảng khác nhau
        $activities = collect();

        // Hoạt động thêm sách
        $bookActivities = Book::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($book) {
                return (object)[
                    'icon' => 'fas fa-book',
                    'icon_bg' => 'bg-blue-100',
                    'icon_text' => 'text-blue-500',
                    'content' => 'Bạn đã đăng sách mới <span class="font-medium">"' . $book->title . '"</span>',
                    'created_at' => $book->created_at
                ];
            });

        // Hoạt động thích
        $likeActivities = DB::table('t_recommends')
            ->join('books', 't_recommends.item_id', '=', 'books.id')
            ->where('t_recommends.user_id', $userId)
            ->where('t_recommends.item_code', 'book')
            ->select('books.title', 't_recommends.created_at')
            ->orderBy('t_recommends.created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($like) {
                return (object)[
                    'icon' => 'fas fa-thumbs-up',
                    'icon_bg' => 'bg-green-100',
                    'icon_text' => 'text-green-500',
                    'content' => 'Bạn đã thích sách <span class="font-medium">"' . $like->title . '"</span>',
                    'created_at' => $like->created_at
                ];
            });

        // Hoạt động bình luận
        $commentActivities = DB::table('t_comments')
            ->join('books', 't_comments.item_id', '=', 'books.id')
            ->where('t_comments.user_id', $userId)
            ->where('t_comments.item_code', 'book')
            ->select('books.title', 't_comments.created_at')
            ->orderBy('t_comments.created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($comment) {
                return (object)[
                    'icon' => 'fas fa-comment',
                    'icon_bg' => 'bg-purple-100',
                    'icon_text' => 'text-purple-500',
                    'content' => 'Bạn đã bình luận về sách <span class="font-medium">"' . $comment->title . '"</span>',
                    'created_at' => $comment->created_at
                ];
            });

        // Gộp tất cả hoạt động
        $activities = $activities->concat($bookActivities)
            ->concat($likeActivities)
            ->concat($commentActivities)
            ->sortByDesc('created_at')
            ->take(5);

        return $activities;
    }

    public function createEdit()
    {
        $data = $this->loadCommonData("Điều chỉnh thông tin tài khoản");
        return view("{$this->front_view}.profile.edit", $data);
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'string|required',
                'address' => 'string|required',
                'phone' => 'string|nullable',
                'photo' => 'string|nullable',
                'banner' => 'string|nullable',
                'description' => 'string|nullable',
                'gender' => 'string|nullable',
                'birthday' => 'date|nullable',
            ]);
            Log::info('FULL REQUEST DATA', $request->all());


            $user = Auth::user();
            $data = $request->except('photo', 'banner', '_token', 'photo_old', 'banner_old');

            // Xử lý ảnh đại diện
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $data['photo'] = $this->storeFile($file, 'avatar', true, $filename);
                Log::info('Đã xử lý file photo', ['path' => $data['photo']]);
            } elseif ($request->photo) {
                $data['photo'] = $request->photo;
            } elseif ($request->photo_old) {
                $data['photo'] = $request->photo_old;
            }

            // Xử lý ảnh bìa
            if ($request->hasFile('banner')) {
                $file = $request->file('banner');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $data['banner'] = $this->storeFile($file, 'banner', true, $filename);
                Log::info('Đã xử lý file banner', ['path' => $data['banner']]);
            } elseif ($request->banner) {
                $data['banner'] = $request->banner;
            } elseif ($request->banner_old) {
                $data['banner'] = $request->banner_old;
            }

            Log::info('Updating user profile', [
                'user_id' => $user->id,
                'data' => $data
            ]);

            if (User::where('id', $user->id)->update($data)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bạn đã cập nhật thành công',
                        'data' => $data
                    ]);
                }
                return redirect()->route('front.profile')->withSuccess('Bạn đã cập nhật thành công');
            } else {
                Log::error("Update profile failed for user ID: {$user->id}");
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lỗi xảy ra khi cập nhật hồ sơ.'
                    ], 500);
                }
                return back()->withError('Lỗi xảy ra khi cập nhật hồ sơ.');
            }
        } catch (\Exception $e) {
            Log::error("Update profile error: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi hệ thống: ' . $e->getMessage()
                ], 500);
            }
            return back()->withError('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    private function storeFile($file, $folder = 'uploads', $useOriginalName = false, $customName = null)
    {
        try {
            if (!$file) return null;

            $path = 'uploads/' . $folder;

            if ($useOriginalName && $customName) {
                $filename = $customName . '_' . time() . '.' . $file->getClientOriginalExtension();
            } else {
                $filename = time() . '_' . $file->getClientOriginalName();
            }

            $file->move(public_path($path), $filename);

            return '/' . $path . '/' . $filename;
        } catch (\Exception $e) {
            Log::error("File upload error: " . $e->getMessage());
            return null;
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|confirmed|min:8|string'
            ], [
                'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
                'new_password.required' => 'Vui lòng nhập mật khẩu mới',
                'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp',
                'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự'
            ]);

            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
            }

            if ($request->current_password === $request->new_password) {
                return back()->withErrors(['new_password' => 'Mật khẩu mới không được trùng với mật khẩu cũ']);
            }

            User::where('id', $user->id)->update(['password' => Hash::make($request->new_password)]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đổi mật khẩu thành công'
                ]);
            }
            
            return back()->with('success', 'Đổi mật khẩu thành công');
        } catch (\Exception $e) {
            Log::error("Change password error: " . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Lỗi hệ thống. Vui lòng thử lại sau.']);
        }
    }

    public function updateName(Request $request)
    {
        return $this->updateUserField($request, ['full_name', 'address']);
    }

    public function updateDescription(Request $request)
    {
        return $this->updateUserField($request, ['description'], ['description' => 'string|nullable']);
    }

    private function updateUserField(Request $request, array $fields, $rules = [])
    {
        try {
            $defaultRules = array_fill_keys($fields, 'string|required');
            $validatedData = $request->validate(array_merge($defaultRules, $rules));

            $user = Auth::user();
            if (User::where('id', $user->id)->update($validatedData)) {
                return back()->withSuccess('Cập nhật thành công');
            } else {
                Log::error("Update failed for user ID: {$user->id}");
                return back()->withError('Lỗi xảy ra');
            }
        } catch (\Exception $e) {
            Log::error("Update field error: " . $e->getMessage());
            return back()->withError('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    public function updateField(Request $request)
    {
        try {
            $user = Auth::user();
            $validFields = ['photo', 'banner', 'full_name', 'address', 'description', 'gender', 'birthday'];

            $data = [];

            // Duyệt qua các field hợp lệ và lấy giá trị nếu tồn tại
            foreach ($validFields as $field) {
                if ($request->has($field)) {
                    $value = $request->get($field); // Dùng get thay vì input
                    if (is_string($value)) { // Đảm bảo là chuỗi (không phải file upload)
                        $data[$field] = $value;
                    }
                }
            }

            if (empty($data)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không có dữ liệu nào được cập nhật'
                ], 400);
            }

            // Ghi log để kiểm tra dữ liệu
            Log::info('Updating user fields', [
                'user_id' => $user->id,
                'data' => $data
            ]);

            // Cập nhật dữ liệu
            if (User::where('id', $user->id)->update($data)) {
                return response()->json([
                    'status' => true,
                    'message' => 'Cập nhật thành công!',
                    'data' => $data
                ]);
            } else {
                Log::error("Update field failed for user ID: {$user->id}");
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi xảy ra khi cập nhật dữ liệu.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Update field error: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function viewUserProfile($id)
    {
        // Lấy thông tin người dùng theo ID
        $profile = User::findOrFail($id);
        
        $isOwner = (Auth::id() == $id);
        
        // Lấy cài đặt riêng tư của người dùng
        $privacySettings = UserPrivacySetting::where('user_id', $id)->first();
        
        $data = [
            'detail' => SettingDetail::find(1),
            'pagetitle' => "Hồ sơ của {$profile->full_name}",
            'profile' => $profile,
            'isOwner' => $isOwner,
            'privacySettings' => $privacySettings
        ];

        $userId = $id;
    
        // Thống kê chung
        $data['bookCount'] = Book::where('user_id', $userId)->count();
        // Tổng lượt xem sách
        $data['totalBookViews'] = DB::table('books')
            ->where('user_id', $userId)
            ->sum('views');
        $data['commentCount'] = DB::table('t_comments')
            ->where('user_id', $userId)
            ->count();
        $data['likeCount'] = DB::table('t_recommends')
            ->where('user_id', $userId)
            ->count();
        $data['postCount'] = DB::table('t_blogs')
            ->where('user_id', $userId)
            ->count();
        // Tổng lượt xem bài viết
        $data['totalBlogViews'] = DB::table('t_blogs')
            ->where('user_id', $userId)
            ->sum('hit');
    
        // Sách của người dùng
        $data['books'] = Book::where('user_id', $userId)
            ->where('status', 'active') // Chỉ hiển thị sách đã được duyệt
            ->orderBy('created_at', 'desc')
            ->paginate(8);
    
        // Sách yêu thích
        $favoriteBookIds = DB::table('t_recommends')
            ->where('user_id', $userId)
            ->where('item_code', 'book')
            ->pluck('item_id')
            ->toArray();
        $data['favoriteBooks'] = Book::whereIn('id', $favoriteBookIds)
            ->where('status', 'active') // Chỉ hiển thị sách đã được duyệt
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    
        // Bài viết của người dùng (chỉ lấy những bài viết công khai)
        $userPosts = DB::table('t_blogs as b')
            ->leftJoin('users as u', 'b.user_id', '=', 'u.id')
            ->where('b.user_id', $userId)
            ->where('b.status', 1) // Chỉ lấy bài viết công khai
            ->select(
                'b.*',
                'u.full_name as author_name',
                'u.photo as author_photo',
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_comments WHERE item_id = b.id AND item_code = "tblog"), 0) as comment_count'),
                DB::raw('COALESCE((SELECT COUNT(*) FROM t_recommends WHERE item_id = b.id AND item_code = "tblog"), 0) as likes_count')
            )
            ->orderBy('b.created_at', 'desc')
            ->limit(3)
            ->get();
    
        // Gắn thêm tags, author, URL và likes_motion_count
        foreach ($userPosts as $post) {
            // Tags
            $post->tags = DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $post->id)
                ->where('t_tag_items.item_code', 'tblog')
                ->select('t_tags.*')
                ->get();
    
            // Kiểm tra user đã like bài viết này chưa (nếu đã đăng nhập)
            if (Auth::check()) {
                $post->user_has_liked = DB::table('t_recommends')
                    ->where('user_id', Auth::id())
                    ->where('item_id', $post->id)
                    ->where('item_code', 'tblog')
                    ->exists();
            } else {
                $post->user_has_liked = false;
            }
    
            // URL profile tác giả
            $post->user_url = route('front.user.profile', $userId);
    
            // Thông tin tác giả
            $post->author = (object)[
                'id' => $post->user_id,
                'full_name' => $post->author_name,
                'photo' => $post->author_photo
            ];
    
            // Tổng reactions từ TMotionItem
            $motionItem = TMotionItem::where('item_id', $post->id)
                ->where('item_code', 'tblog')
                ->first();
            $post->likes_motion_count = $motionItem
                ? $motionItem->getTotalReactionsCount()
                : 0;
                
            // Tính tổng số like (cả recommends và motion_items)
            $post->total_likes = $post->likes_count + $post->likes_motion_count;
                
            // Kiểm tra xem người dùng đã bookmark bài viết chưa (nếu đã đăng nhập)
            if (Auth::check()) {
                $post->is_bookmarked = TRecommend::hasBookmarked($post->id, 'tblog');
            } else {
                $post->is_bookmarked = false;
            }
        }
        $data['userPosts'] = $userPosts;
        
        // Số lượng yêu thích
        $data['likeBookCount'] = count($favoriteBookIds);
        
        // Followers count
        $data['followersCount'] = 0; // Cần thêm logic đếm follower nếu có
        
        // Bài viết yêu thích
        $data['favoritePosts'] = DB::table('t_blogs as b')
            ->select('b.*', 't_recommends.created_at as recommend_date')
            ->join('t_recommends', function ($join) use ($userId) {
                $join->on('b.id', '=', 't_recommends.item_id')
                    ->where('t_recommends.item_code', 'tblog')
                    ->where('t_recommends.user_id', $userId);
            })
            ->where('b.status', 1) // Chỉ lấy bài viết có status = 1
            ->orderBy('recommend_date', 'desc') // Sắp xếp theo thời gian yêu thích mới nhất
            ->limit(5) // Giới hạn 5 bài viết
            ->get();
        
        return view("{$this->front_view}.profile.profile", $data);
    }

    public function updatePrivacySettings(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate incoming request data
            $validated = $request->validate([
                'hide_posts' => 'boolean',
                'hide_personal_info' => 'boolean',
                'hide_books' => 'boolean',
                'hide_favorites' => 'boolean',
            ]);
            
            // Find or create the privacy settings for this user
            $privacySettings = UserPrivacySetting::updateOrCreate(
                ['user_id' => $user->id],
                $validated
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Cài đặt riêng tư đã được cập nhật thành công!',
                'data' => $privacySettings
            ]);
        } catch (\Exception $e) {
            Log::error("Privacy settings update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateTotalLikes($postId) {
        // Đếm số lượt like từ bảng t_recommends
        $recommendCount = DB::table('t_recommends')
            ->where('item_id', $postId)
            ->where('item_code', 'tblog')
            ->count();
            
        // Đếm số lượt like từ bảng t_motion_items
        $motionItem = TMotionItem::where('item_id', $postId)
            ->where('item_code', 'tblog')
            ->first();
        $motionCount = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
        
        // Tổng số lượt like
        return $recommendCount + $motionCount;
    }
}

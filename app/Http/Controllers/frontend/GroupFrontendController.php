<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Modules\Group\Models\Group;
use App\Modules\Group\Models\GroupType;
use App\Modules\Group\Models\GroupMember;
use App\Modules\Tuongtac\Models\TBlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Poll;
use App\Models\PollVote;
use App\Modules\Tuongtac\Models\TMotionItem;
use App\Modules\Tuongtac\Models\TRecommend;
use App\Modules\Tuongtac\Models\TMotion;
use App\Models\Rating;

class GroupFrontendController extends Controller
{
    const REQUIRED_POINTS_TO_CREATE_GROUP = 0; // Số điểm cần thiết để tạo group

    public function index(Request $request)
    {
        // Lấy danh sách loại nhóm để hiển thị bộ lọc
        $groupTypes = GroupType::where('status', 'active')->get();
        
        // Lấy loại nhóm được chọn từ request (nếu có)
        $selectedType = $request->input('type_code');
        $keyword = $request->input('keyword');
        
        // Base query cho nhóm công khai 
        $publicGroupsQuery = Group::where('status', 'active')
            ->where('is_private', 0);

        // Base query cho nhóm riêng tư
        $privateGroupsQuery = Group::where('status', 'active')
            ->where('is_private', 1);
            
        // Áp dụng bộ lọc loại nhóm nếu có
        if ($selectedType) {
            $publicGroupsQuery->where('type_code', $selectedType);
            $privateGroupsQuery->where('type_code', $selectedType);
        }
        
        // Áp dụng bộ lọc từ khóa tìm kiếm
        if ($keyword) {
            $publicGroupsQuery->where(function($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
            });
            
            $privateGroupsQuery->where(function($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // Thực thi truy vấn và phân trang
        $publicGroups = $publicGroupsQuery->orderBy('id', 'DESC')->paginate(10);
        $privateGroups = $privateGroupsQuery->orderBy('id', 'DESC')->paginate(10);

        $userGroups = [];
        if (Auth::check()) {
            $userId = Auth::id();
            // Lấy tất cả nhóm mà user là thành viên
            $groupsQuery = Group::where('status', 'active');
            
            // Áp dụng bộ lọc loại nhóm và từ khóa nếu có
            if ($selectedType) {
                $groupsQuery->where('type_code', $selectedType);
            }
            
            if ($keyword) {
                $groupsQuery->where(function($query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%")
                          ->orWhere('description', 'like', "%{$keyword}%");
                });
            }
            
            $groups = $groupsQuery->get();

            foreach ($groups as $group) {
                $members = json_decode($group->members ?? '[]', true);
                if (in_array($userId, $members) || $group->author_id == $userId) {
                    $userGroups[] = $group;
                }
            }
            
            // Add rating data to each user group
            foreach ($userGroups as $group) {
                $this->addRatingData($group);
            }
        }
        
        // Add rating data to each group
        foreach ($publicGroups as $group) {
            $this->addRatingData($group);
        }

        foreach ($privateGroups as $group) {
            $this->addRatingData($group);
        }

        // Thống kê số lượng nhóm theo loại
        $groupTypeStats = [];
        foreach ($groupTypes as $type) {
            $groupTypeStats[$type->type_code] = [
                'title' => $type->title,
                'count' => Group::where('type_code', $type->type_code)
                    ->where('status', 'active')
                    ->count()
            ];
        }

        return view('Tuongtac::frontend.group.index', compact(
            'publicGroups', 
            'privateGroups', 
            'userGroups',
            'groupTypes',
            'selectedType',
            'keyword',
            'groupTypeStats'
        ));
    }

    public function create()
    {
        try {
            // Thêm log để debug
            Log::info('Accessing create group page', [
                'user_id' => Auth::check() ? Auth::id() : 'guest',
                'timestamp' => now()
            ]);
            
            if (!Auth::check()) {
                Log::info('User not logged in, redirecting to login');
                return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để tạo nhóm.');
            }

            $user = Auth::user();
            
            // Log thông tin điểm của người dùng
            Log::info('Checking user points for group creation', [
                'user_id' => $user->id,
                'user_points' => $user->totalpoint,
                'required_points' => self::REQUIRED_POINTS_TO_CREATE_GROUP,
                'has_enough_points' => $user->totalpoint >= self::REQUIRED_POINTS_TO_CREATE_GROUP
            ]);
            
            // Kiểm tra điểm người dùng
            if ($user->totalpoint < self::REQUIRED_POINTS_TO_CREATE_GROUP) {
                Log::info('User has insufficient points, showing message');
                return $this->showInsufficientPointsMessage($user);
            }

            Log::info('User has enough points, proceeding to group creation form');
            $groupTypes = GroupType::where('status', 'active')->get();
            return view('Tuongtac::frontend.group.create', compact('groupTypes'));
        } catch (\Exception $e) {
            Log::error('Error in create group method: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('group.index')
                ->with('error', 'Đã xảy ra lỗi khi truy cập trang tạo nhóm: ' . $e->getMessage());
        }
    }

    // Phương thức mới để hiển thị thông báo thiếu điểm chi tiết
    protected function showInsufficientPointsMessage($user)
    {
        $missingPoints = self::REQUIRED_POINTS_TO_CREATE_GROUP - $user->totalpoint;
        
        // Tạo thông báo chi tiết
        $message = 'Bạn cần có ít nhất ' . self::REQUIRED_POINTS_TO_CREATE_GROUP . ' điểm để tạo nhóm. ';
        $message .= 'Hiện tại bạn có ' . $user->totalpoint . ' điểm, còn thiếu ' . $missingPoints . ' điểm. ';
        $message .= 'Hãy tích cực tham gia đóng góp trên hệ thống để nhận thêm điểm!';
        
        // Log thông báo sẽ hiển thị
        Log::info('Showing insufficient points message', [
            'user_id' => $user->id,
            'missing_points' => $missingPoints,
            'message' => $message
        ]);
        
        // Chuyển hướng với thông báo chi tiết
        return redirect()->route('group.index')->with('warning', $message);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để tạo nhóm.');
        }

        $user = Auth::user();
        // Kiểm tra điểm người dùng
        if ($user->totalpoint < self::REQUIRED_POINTS_TO_CREATE_GROUP) {
            return $this->showInsufficientPointsMessage($user);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'photo' => 'nullable|string',
        ]);

        // Debug: Log giá trị của $request->photo
        Log::info('Store Group - Photo value from request:', [
            'photo' => $request->photo
        ]);

        // Tạo slug từ title
        $slug = Str::slug($request->title);

        // Kiểm tra slug đã tồn tại chưa
        $slug_count = Group::where('slug', $slug)->count();
        if ($slug_count > 0) {
            $slug = $slug . '-' . uniqid();
        }

        $group = new Group();
        $group->title = $request->title;
        $group->slug = $slug;
        $group->type_code = $request->type_code;
        $group->description = $request->description ?? '';
        $group->status = 'active';
        $group->is_private = $request->has('is_private') ? 1 : 0;
        $group->author_id = Auth::id();
        $group->members = json_encode([Auth::id()]); // Người tạo tự động là thành viên
        $group->pending_members = json_encode([]);
        $group->moderators = json_encode([]); // Chưa có phó nhóm

        // Sử dụng ảnh đại diện đã được tải lên bởi Dropzone
        if ($request->photo) {
            $group->photo = $request->photo;
            Log::info('Group photo has been set to:', ['photo' => $group->photo]);
        } else {
            $group->photo = asset('backend/assets/dist/images/profile-6.jpg');
            Log::info('Using default photo for group');
        }

        $group->save();

        // Tạo bản ghi thành viên cho người tạo nhóm với quyền admin
        GroupMember::create([
            'user_id' => Auth::id(),
            'group_id' => $group->id,
            'role' => 'admin',
            'status' => 'active'
        ]);

        return redirect()->route('group.show', $group->id)->with('success', 'Nhóm đã được tạo thành công!');
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);
        
        // Debug group information
        Log::info("Group information:", [
            'id' => $group->id,
            'title' => $group->title,
            'is_private' => $group->is_private,
            'type' => $group->type ?? 'null'
        ]);

        // Khởi tạo các biến mặc định
        $members = collect([]);
        $moderators = collect([]);
        $posts = collect([]);
        $joinRequests = [];
        $topPosts = collect([]);
        $activeMembers = collect([]);
        $polls = []; // Khởi tạo biến $polls ngay từ đầu

        // Kiểm tra trạng thái isMember và chuẩn bị joinRequest
        $isMember = false;
        $joinRequest = null;
        $isAdmin = false;
        $isModerator = false;

        if (Auth::check()) {
            $userId = Auth::id();
            $members = json_decode($group->members ?? '[]', true);
            $moderatorIds = json_decode($group->moderators ?? '[]', true);
            
            $isAdmin = $userId == $group->author_id;
            $isModerator = in_array($userId, $moderatorIds);
            $isMember = in_array($userId, $members) || $isAdmin;
            
            // Kiểm tra yêu cầu tham gia
            $pendingMembers = json_decode($group->pending_members ?? '[]', true);
            if (in_array($userId, $pendingMembers)) {
                $joinRequest = (object)['status' => 'pending'];
            }
        }

        // Lấy thông tin đánh giá
        $this->addRatingData($group);

        // Lấy danh sách thành viên đã được duyệt (chỉ khi là thành viên hoặc nhóm công khai)
        $members = collect([]);
        $moderators = collect([]);
        $posts = collect([]);
        $joinRequests = [];

        // Lấy 5 bài viết nổi bật (nhiều tương tác nhất)
        $topPosts = collect([]);
        
        // Lấy 5 thành viên tích cực nhất (đăng bài và tương tác nhiều nhất)
        $activeMembers = collect([]);

        // Nếu là thành viên hoặc nhóm công khai, hiển thị đầy đủ thông tin
        if ($isMember || $group->is_private == 0) {
            Log::info("Đủ điều kiện xem bài viết: isMember={$isMember}, is_private={$group->is_private}");
            
            // Lấy danh sách thành viên đã được duyệt kèm thông tin tham gia từ bảng group_members
            $memberIds = json_decode($group->members ?? '[]', true);
            
            $members = User::whereIn('id', $memberIds)
                ->with(['groupMember' => function($query) use ($id) {
                    $query->where('group_id', $id);
                }])
                ->get();
                
            // Thêm trường joined_at từ group_members vào đối tượng user
            foreach ($members as $member) {
                if ($member->groupMember) {
                    $member->joined_at = $member->groupMember->created_at;
                    $member->role_in_group = $member->groupMember->role;
                }
            }
            
            // Log thông tin members để debug
            Log::info("Members data loaded:", [
                'count' => $members->count(),
                'first_member' => $members->first() ? [
                    'id' => $members->first()->id,
                    'name' => $members->first()->name,
                    'joined_at' => $members->first()->joined_at ?? 'not set',
                    'role_in_group' => $members->first()->role_in_group ?? 'not set'
                ] : null
            ]);
            
            // Lấy danh sách phó nhóm
            $moderatorIds = json_decode($group->moderators ?? '[]', true);
            $moderators = User::whereIn('id', $moderatorIds)->get();
            
            // Lấy danh sách bài viết của nhóm
            try {
                Log::info("Đang lấy bài viết cho nhóm: " . $id);
                
                $posts = TBlog::with(['author'])
                          ->where('group_id', $id)
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);
                
                Log::info("Kết quả truy vấn posts: " . $posts->count() . " bài viết");

                // Tiến hành xử lý thông tin cho từng bài viết
                if ($posts->count() > 0) {
                    foreach ($posts as $post) {
                        // Debug info
                        Log::info("Xử lý bài viết ID: " . $post->id . ", Tiêu đề: " . $post->title ?? 'Không có tiêu đề');
                        
                        // Xử lý ảnh
                        if (!empty($post->photo)) {
                            $photos = json_decode($post->photo, true);
                            $post->photo = is_array($photos) ? $photos : null;
                        } else {
                            $post->photo = null;
                        }
                        
                        // Lấy tags của bài viết
                        $post->tags = DB::table('t_tags')
                            ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                            ->where('t_tag_items.item_id', $post->id)
                            ->where('t_tag_items.item_code', 'tblog')
                            ->select('t_tags.*')
                            ->get();

                        // Thêm URL cho user
                        $post->user_url = route('front.user.profile', ['id' => $post->author->id]);
                        // Lấy số lượng likes cho bài viết
                        $motionItem = TMotionItem::where('item_id', $post->id)
                            ->where('item_code', 'tblog')
                            ->first();

                        $post->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
                        
                        
                        // $post->likes_count = DB::table('t_motion_items')
                        //     ->where('item_id', $post->id)
                        //     ->where('item_code', 'tblog')
                        //     ->count();
                            
                        // Lấy số lượng comments cho bài viết
                        $post->comments_count = DB::table('t_comments')
                            ->where('item_id', $post->id)
                            ->where('item_code', 'tblog')
                            ->count();

                        // Kiểm tra xem người dùng đã yêu thích bài viết chưa
                        $post->is_bookmarked = Auth::check() ? TRecommend::hasBookmarked($post->id, 'tblog') : false;
                        $post->user_has_liked = Auth::check() ? TMotion::checkUserReacted($post->id, 'tblog', Auth::id()) : false;

                        // Thêm action bar HTML nếu view partial tồn tại
                        try {
                            $post->actionbar = view('Tuongtac::frontend.blogs._action_bar', [
                                'post' => $post,
                                'user' => Auth::user()
                            ])->render();
                        } catch (\Exception $e) {
                            $post->actionbar = '';
                        }

                        // Thêm comment HTML nếu view partial tồn tại
                        try {
                            $post->commenthtml = view('Tuongtac::frontend.blogs._comments', [
                                'post' => $post,
                                'user' => Auth::user()
                            ])->render();
                        } catch (\Exception $e) {
                            $post->commenthtml = '';
                        }
                    }
                }

                // Lấy 5 bài viết nổi bật dựa trên số lượng like và comment
                $topPostsQuery = TBlog::with(['author'])
                    ->where('group_id', $id)
                    ->select('t_blogs.*', DB::raw('(SELECT COUNT(*) FROM t_motion_items WHERE t_motion_items.item_id = t_blogs.id AND t_motion_items.item_code = "tblog") as likes_count'))
                    ->orderBy('likes_count', 'desc')
                    ->limit(5);

                // Thực hiện truy vấn lấy danh sách bài viết nổi bật
                $topPosts = $topPostsQuery->get();

                foreach ($topPosts as $post) {
                    // Xử lý ảnh cho bài viết nổi bật
                    if (!empty($post->photo)) {
                        $photos = json_decode($post->photo, true);
                        if (is_array($photos) && !empty($photos)) {
                            $post->thumbnail = $photos[0];
                        } else {
                            $post->thumbnail = asset('backend/assets/dist/images/preview-1.jpg');
                        }
                    } else {
                        $post->thumbnail = asset('backend/assets/dist/images/preview-1.jpg');
                    }
                    
                    // Lấy số lượng comment cho bài viết
                    $post->comments_count = DB::table('t_comments')
                        ->where('item_id', $post->id)
                        ->where('item_code', 'tblog')
                        ->count();
                        
                    // URL chi tiết bài viết
                    $post->url = route('front.tblogs.show', $post->id);
                }
                
                // Lấy 5 thành viên tích cực nhất
                $activeMembers = User::whereIn('id', $memberIds)
                    ->withCount(['blogs' => function($query) use ($id) {
                        $query->where('group_id', $id);
                    }])
                    ->orderBy('blogs_count', 'desc')
                    ->limit(5)
                    ->get();
                    
                foreach ($activeMembers as $member) {
                    // URL profile người dùng
                    $member->url = route('front.user.profile', $member->id);
                    
                    // Số lượng tương tác (comment + like) của thành viên
                    $member->interactions_count = 
                        DB::table('t_comments')
                            ->where('user_id', $member->id)
                            ->where('item_code', 'tblog')
                            ->whereIn('item_id', function($query) use ($id) {
                                $query->select('id')
                                    ->from('t_blogs')
                                    ->where('group_id', $id);
                            })
                            ->count() + 
                        DB::table('t_motion_items')
                            ->where('item_code', 'tblog')
                            ->whereIn('item_id', function($query) use ($id) {
                                $query->select('id')
                                    ->from('t_blogs')
                                    ->where('group_id', $id);
                            })
                            ->count();
                }

            } catch (\Exception $e) {
                Log::error('Error fetching posts: ' . $e->getMessage());
                $posts = collect([]);
            }

            // Lấy danh sách các khảo sát (polls) liên quan đến group nếu có
            $polls = [];
            try {
                // Chỉ thực hiện nếu bảng polls tồn tại
                if (Schema::hasTable('polls')) {
                    Log::info("Đang lấy danh sách khảo sát cho nhóm: " . $id);
                    
                    // Lấy tất cả các khảo sát của nhóm này, kèm theo options, votes và người tạo
                    $pollsData = Poll::with(['options', 'votes', 'creator'])
                                ->where('group_id', $id)
                                ->latest()
                                ->get();
                    
                    Log::info("Kết quả truy vấn polls: " . $pollsData->count() . " khảo sát");
                    
                    // Xử lý dữ liệu cho từng khảo sát
                    foreach ($pollsData as $poll) {
                        // Kiểm tra xem người dùng hiện tại đã bình chọn chưa
                        $hasVoted = false;
                        $userVote = null;
                        
                        if (Auth::check()) {
                            $hasVoted = $poll->hasUserVoted(Auth::id());
                            if ($hasVoted) {
                                $userVote = PollVote::where('poll_id', $poll->id)
                                    ->where('user_id', Auth::id())
                                    ->first();
                            }
                        }
                        
                        // Tính tổng số phiếu bầu và kết quả
                        $totalVotes = $poll->getTotalVotesCount();
                        $results = $poll->getVotesCountByOption();
                        
                        // Chuẩn bị dữ liệu khảo sát
                        $pollData = [
                            'id' => $poll->id,
                            'title' => $poll->title,
                            'question' => $poll->question,
                            'created_at' => $poll->created_at,
                            'expires_at' => $poll->expires_at,
                            'creator' => $poll->creator,
                            'options' => $poll->options,
                            'has_voted' => $hasVoted,
                            'user_vote' => $userVote,
                            'total_votes' => $totalVotes,
                            'results' => $results,
                            'is_expired' => $poll->isExpired()
                        ];
                        
                        $polls[] = $pollData;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error fetching polls: ' . $e->getMessage());
            }

            // Lấy danh sách yêu cầu tham gia (chỉ dành cho admin và moderator)
            if ($isAdmin || $isModerator) {
                $pendingIds = json_decode($group->pending_members ?? '[]', true);
                if (!empty($pendingIds)) {
                    $pendingUsers = User::whereIn('id', $pendingIds)->get();
                    foreach ($pendingUsers as $user) {
                        $joinRequests[] = (object)[
                            'id' => $user->id,
                            'user' => $user,
                            'created_at' => now()
                        ];
                    }
                }
            }
        }

        // Xác định vai trò người dùng
        $userRole = null;
        if ($isAdmin) {
            $userRole = 'admin';
        } elseif ($isModerator) {
            $userRole = 'moderator';
        } elseif ($isMember) {
            $userRole = 'member';
        }

        // Kết hợp bài viết và khảo sát thành một mảng chung
        $combinedContent = collect();
        
        // Thêm bài viết vào mảng
        if (!empty($posts) && $posts->count() > 0) {
            foreach ($posts as $post) {
                $post->content_type = 'post';
                $combinedContent->push($post);
            }
        }
        
        // Thêm khảo sát vào mảng
        if (!empty($polls) && count($polls) > 0) {
            foreach ($polls as $poll) {
                // Chuyển đổi mảng thành đối tượng nếu $poll là mảng
                if (is_array($poll)) {
                    $pollObj = (object) $poll;
                    $pollObj->content_type = 'poll';
                    $combinedContent->push($pollObj);
                } else {
                    // Nếu đã là đối tượng thì gán trực tiếp
                    $poll->content_type = 'poll';
                    $combinedContent->push($poll);
                }
            }
        }
        
        // Sắp xếp theo thời gian tạo (mới nhất lên đầu)
        $sortedContent = $combinedContent->sortByDesc('created_at');
        
        return view('Tuongtac::frontend.group.show', compact(
            'group',
            'members',
            'moderators',
            'posts',
            'userRole',
            'isMember',
            'joinRequest',
            'joinRequests',
            'isAdmin',
            'isModerator',
            'topPosts',
            'activeMembers',
            'polls',
            'sortedContent' // Thêm dữ liệu đã sắp xếp vào view
        ));
    }

    public function edit($id)
    {
        $group = Group::findOrFail($id);

        if (!Auth::check() || Auth::id() != $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa nhóm này.');
        }

        $groupTypes = GroupType::where('status', 'active')->get();
        return view('Tuongtac::frontend.group.edit', compact('group', 'groupTypes'));
    }

    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        // Kiểm tra quyền chỉnh sửa
        if (Auth::id() != $group->author_id && Auth::user()->role != 'admin') {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa nhóm này!');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'photo' => 'nullable|string',
            'banner' => 'nullable|string', // Thêm trường banner (thay thế cho cover_photo)
            'cover_photo' => 'nullable|string', // Giữ lại cover_photo cho tương thích ngược
        ]);

        // Debug: Log giá trị của request
        Log::info('Update Group - Request values:', [
            'photo' => $request->photo,
            'banner' => $request->banner,
            'cover_photo' => $request->cover_photo
        ]);

        // Cập nhật slug nếu tiêu đề thay đổi
        if ($group->title != $request->title) {
            $slug = Str::slug($request->title);
            $slug_count = Group::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($slug_count > 0) {
                $slug = $slug . '-' . uniqid();
            }
            $group->slug = $slug;
        }

        $group->title = $request->title;
        $group->type_code = $request->type_code;
        $group->description = $request->description ?? '';
        $group->is_private = $request->has('is_private') ? 1 : 0;

        // Xử lý ảnh đại diện từ Dropzone
        if ($request->photo) {
            $group->photo = $request->photo;
            Log::info('Group photo has been updated to:', ['photo' => $group->photo]);
        }

        // Xử lý ảnh bìa (banner)
        // Ưu tiên sử dụng trường 'banner' nếu có, nếu không thì dùng 'cover_photo'
        if ($request->banner) {
            // Lưu vào cả banner và cover_photo để đảm bảo tương thích
            Log::info('Đã nhận request->banner:', ['banner' => $request->banner]);
            $group->cover_photo = $request->banner;
            Log::info('Group cover_photo has been updated to:', ['cover_photo' => $group->cover_photo]);
        } elseif ($request->cover_photo) {
            // Lưu vào cover_photo
            Log::info('Đã nhận request->cover_photo:', ['cover_photo' => $request->cover_photo]);
            $group->cover_photo = $request->cover_photo;
            Log::info('Group cover_photo has been updated to:', ['cover_photo' => $group->cover_photo]);
        }

        $group->save();
        
        Log::info('Group has been updated:', [
            'id' => $group->id,
            'title' => $group->title,
            'photo' => $group->photo,
            'cover_photo' => $group->cover_photo
        ]);

        return redirect()->route('group.show', $id)->with('success', 'Nhóm đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        $group = Group::findOrFail($id);
        if (!Auth::check() || Auth::id() != $group->author_id) {
            return redirect()->route('group.index')->with('error', 'Bạn không có quyền xóa nhóm này.');
        }

        // Xóa tất cả bản ghi liên quan
        GroupMember::where('group_id', $id)->delete();

        // Thử xóa các bài viết liên quan đến nhóm
        try {
            TBlog::where('group_id', $id)->delete();
        } catch (\Exception $e) {
            // Bỏ qua lỗi nếu không có cột group_id trong bảng TBlog
        }

        $group->delete();

        return redirect()->route('group.index')->with('success', 'Nhóm đã được xóa thành công!');
    }

    public function requestJoinGroup($id)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để yêu cầu tham gia nhóm.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra nếu người dùng đã là thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (in_array($userId, $members) || $userId == $group->author_id) {
            return redirect()->route('group.show', $id)->with('info', 'Bạn đã là thành viên của nhóm này.');
        }

        // Kiểm tra nếu đã yêu cầu tham gia trước đó
        $pendingMembers = json_decode($group->pending_members ?? '[]', true);
        if (in_array($userId, $pendingMembers)) {
            return redirect()->route('group.show', $id)->with('info', 'Bạn đã gửi yêu cầu tham gia nhóm và đang chờ phê duyệt.');
        }

        // Thêm vào danh sách chờ duyệt
        $pendingMembers[] = $userId;
        $group->pending_members = json_encode(array_values($pendingMembers));
        $group->save();

        return redirect()->route('group.show', $id)->with('success', 'Yêu cầu tham gia nhóm đã được gửi và đang chờ phê duyệt.');
    }

    public function approveMember(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra quyền (chỉ admin hoặc moderator mới có quyền duyệt)
        if ($userId != $group->author_id && !in_array($userId, json_decode($group->moderators ?? '[]', true))) {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền duyệt thành viên.');
        }

        // Kiểm tra user_id có tồn tại trong request
        if (!$request->has('user_id')) {
            return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        $memberUserId = $request->user_id;

        // Cập nhật danh sách thành viên
        $pendingMembers = json_decode($group->pending_members ?? '[]', true);
        $members = json_decode($group->members ?? '[]', true);

        // Chỉ duyệt nếu user có trong danh sách pending
        if (($key = array_search($memberUserId, $pendingMembers)) !== false) {
            unset($pendingMembers[$key]);
            if (!in_array($memberUserId, $members)) {
                $members[] = $memberUserId;
        }

        $group->pending_members = json_encode(array_values($pendingMembers));
        $group->members = json_encode(array_values($members));
        $group->save();

            // Tạo bản ghi thành viên
            GroupMember::updateOrCreate(
                ['user_id' => $memberUserId, 'group_id' => $group->id],
                ['role' => 'member', 'status' => 'active']
            );

            return redirect()->route('group.show', $id)->with('success', 'Thành viên đã được duyệt thành công.');
        }

        return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy yêu cầu tham gia của người dùng này.');
    }

    public function rejectMember(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra quyền (chỉ admin hoặc moderator mới có quyền từ chối)
        if ($userId != $group->author_id && !in_array($userId, json_decode($group->moderators ?? '[]', true))) {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền từ chối yêu cầu tham gia.');
        }

        // Kiểm tra user_id có tồn tại trong request
        if (!$request->has('user_id')) {
            return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        $memberUserId = $request->user_id;

        // Cập nhật danh sách thành viên chờ duyệt
        $pendingMembers = json_decode($group->pending_members ?? '[]', true);
        
        // Chỉ từ chối nếu user có trong danh sách pending
        if (($key = array_search($memberUserId, $pendingMembers)) !== false) {
            unset($pendingMembers[$key]);
            $group->pending_members = json_encode(array_values($pendingMembers));
            $group->save();

            return redirect()->route('group.show', $id)->with('success', 'Đã từ chối yêu cầu tham gia.');
        }

        return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy yêu cầu tham gia của người dùng này.');
    }

    public function removeMember(Request $request, $id, $user_id = null)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này.'], 401);
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Chỉ admin (trưởng nhóm) mới có quyền xóa thành viên
        if ($userId != $group->author_id) {
            return response()->json(['success' => false, 'message' => 'Chỉ trưởng nhóm mới có quyền xóa thành viên.'], 403);
        }

        // Lấy user_id từ params hoặc request
        $memberUserId = $user_id ?? $request->user_id;
        if (empty($memberUserId)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.'], 400);
        }

        // Không thể xóa người tạo nhóm
        if ($memberUserId == $group->author_id) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa người tạo nhóm.'], 400);
        }

        // Cập nhật danh sách thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (($key = array_search($memberUserId, $members)) !== false) {
            unset($members[$key]);
        }

        // Đồng thời xóa khỏi danh sách moderator nếu có
        $moderators = json_decode($group->moderators ?? '[]', true);
        if (($key = array_search($memberUserId, $moderators)) !== false) {
            unset($moderators[$key]);
        }

        $group->members = json_encode(array_values($members));
        $group->moderators = json_encode(array_values($moderators));
        $group->save();

        // Cập nhật bản ghi thành viên
        GroupMember::where('user_id', $memberUserId)
            ->where('group_id', $group->id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Thành viên đã bị xóa khỏi nhóm.']);
    }

    public function promoteModerator(Request $request, $id, $user_id = null)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này.'], 401);
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Chỉ admin mới có quyền phong phó nhóm
        if ($userId != $group->author_id) {
            return response()->json(['success' => false, 'message' => 'Chỉ trưởng nhóm mới có quyền phong phó nhóm.'], 403);
        }

        // Lấy user_id từ params hoặc request
        $memberUserId = $user_id ?? $request->user_id;
        if (empty($memberUserId)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.'], 400);
        }

        // Kiểm tra xem người được phong có phải là thành viên không
        $members = json_decode($group->members ?? '[]', true);
        if (!in_array($memberUserId, $members)) {
            return response()->json(['success' => false, 'message' => 'Người dùng không phải là thành viên của nhóm.'], 400);
        }

        // Cập nhật danh sách phó nhóm
        $moderators = json_decode($group->moderators ?? '[]', true);
        if (!in_array($memberUserId, $moderators)) {
            $moderators[] = $memberUserId;
            $group->moderators = json_encode(array_values($moderators));
            $group->save();

            // Cập nhật vai trò trong bảng GroupMember
            GroupMember::where('user_id', $memberUserId)
                ->where('group_id', $group->id)
                ->update(['role' => 'moderator']);

            return response()->json(['success' => true, 'message' => 'Thành viên đã được phong làm phó nhóm.']);
        }

        return response()->json(['success' => false, 'message' => 'Thành viên này đã là phó nhóm.']);
    }

    public function demoteModerator(Request $request, $id, $user_id = null)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này.'], 401);
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Chỉ admin mới có quyền hạ cấp phó nhóm
        if ($userId != $group->author_id) {
            return response()->json(['success' => false, 'message' => 'Chỉ trưởng nhóm mới có quyền hạ cấp phó nhóm.'], 403);
        }

        // Lấy user_id từ params hoặc request
        $memberUserId = $user_id ?? $request->user_id;
        if (empty($memberUserId)) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin người dùng.'], 400);
        }

        // Cập nhật danh sách phó nhóm
        $moderators = json_decode($group->moderators ?? '[]', true);
        if (($key = array_search($memberUserId, $moderators)) !== false) {
            unset($moderators[$key]);
            $group->moderators = json_encode(array_values($moderators));
            $group->save();

            // Cập nhật vai trò trong bảng GroupMember
            GroupMember::where('user_id', $memberUserId)
                ->where('group_id', $group->id)
                ->update(['role' => 'member']);

            return response()->json(['success' => true, 'message' => 'Phó nhóm đã được hạ cấp thành thành viên thường.']);
        }

        return response()->json(['success' => false, 'message' => 'Người dùng này không phải là phó nhóm.']);
    }

    public function leaveGroup($id)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Người tạo nhóm không thể rời nhóm
        if ($userId == $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Người tạo nhóm không thể rời nhóm. Hãy xóa nhóm hoặc chuyển quyền cho người khác trước.');
        }

        // Xóa khỏi danh sách thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (($key = array_search($userId, $members)) !== false) {
            unset($members[$key]);
        }

        // Xóa khỏi danh sách phó nhóm nếu có
        $moderators = json_decode($group->moderators ?? '[]', true);
        if (($key = array_search($userId, $moderators)) !== false) {
            unset($moderators[$key]);
        }

        $group->members = json_encode(array_values($members));
        $group->moderators = json_encode(array_values($moderators));
        $group->save();

        // Xóa bản ghi thành viên
        GroupMember::where('user_id', $userId)
            ->where('group_id', $group->id)
            ->delete();

        return redirect()->route('group.index')->with('success', 'Bạn đã rời khỏi nhóm thành công.');
    }

    public function vote(Request $request)
    {
        $request->validate([
            'point' => 'required|integer|min:1|max:5',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'msg' => 'Bạn cần đăng nhập!']);
        }
        
        $point = $request->point;
        $groupId = $request->group_id;
        
        // Sử dụng RatingService để tạo/cập nhật đánh giá
        $ratingService = app(\App\Services\RatingService::class);
        $rating = $ratingService->createOrUpdateRating($groupId, $userId, $point);
        
        return response()->json([
            'success' => true, 
            'averagePoint' => $rating->book->average_rating,
            'count' => $rating->book->rating_count
        ]);
    }

    // Phương thức hỗ trợ để thêm dữ liệu đánh giá vào group
    private function addRatingData($group)
    {
        $group->vote_count = $group->rating_count ?? 0;
        $group->vote_average = $group->average_rating ?? 0;

        // Nếu đăng nhập, lấy đánh giá của người dùng
        if (Auth::check()) {
            $userId = Auth::id();
            $userRating = Rating::where('book_id', $group->id)
                ->where('user_id', $userId)
                ->first();
            $group->user_vote = $userRating?->rating ?? null;
        }

        return $group;
    }

    public function join($id)
    {
        if (!Auth::check()) {
            return redirect()->route('front.login')->with('error', 'Bạn cần đăng nhập để tham gia nhóm.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra nếu người dùng đã là thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (in_array($userId, $members) || $userId == $group->author_id) {
            return redirect()->route('group.show', $id)->with('info', 'Bạn đã là thành viên của nhóm này.');
        }

        // Kiểm tra nếu đã yêu cầu tham gia trước đó
        $pendingMembers = json_decode($group->pending_members ?? '[]', true);
        if (in_array($userId, $pendingMembers)) {
            return redirect()->route('group.show', $id)->with('info', 'Bạn đã gửi yêu cầu tham gia nhóm và đang chờ phê duyệt.');
        }

        // Nếu nhóm không riêng tư, thêm ngay vào danh sách thành viên
        if ($group->is_private == 0) {
            $members[] = $userId;
            $group->members = json_encode(array_values($members));
            $group->save();

            // Tạo bản ghi thành viên
            GroupMember::create([
                'user_id' => $userId,
                'group_id' => $group->id,
                'role' => 'member',
                'status' => 'active'
            ]);

            return redirect()->route('group.show', $id)->with('success', 'Bạn đã tham gia nhóm thành công!');
        }

        // Nếu là nhóm riêng tư, thêm vào danh sách chờ duyệt
        $pendingMembers[] = $userId;
        $group->pending_members = json_encode(array_values($pendingMembers));
        $group->save();

        return redirect()->route('group.show', $id)->with('success', 'Yêu cầu tham gia nhóm đã được gửi và đang chờ duyệt.');
    }
}

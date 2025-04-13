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

class GroupFrontendController extends Controller
{
    const REQUIRED_POINTS_TO_CREATE_GROUP = 0; // Số điểm cần thiết để tạo group

    public function index()
    {
        // Hiển thị tất cả nhóm công khai 
        $publicGroups = Group::where('status', 'active')
            ->where('is_private', 0)
            ->orderBy('id', 'DESC')
            ->paginate(10);

        // Hiển thị tất cả nhóm riêng tư - có thể xem được thông tin cơ bản
        $privateGroups = Group::where('status', 'active')
            ->where('is_private', 1)
            ->orderBy('id', 'DESC')
            ->paginate(10);

        $userGroups = [];
        if (Auth::check()) {
            $userId = Auth::id();
            // Lấy tất cả nhóm mà user là thành viên
            $groups = Group::where('status', 'active')->get();

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

        return view('Tuongtac::frontend.group.index', compact('publicGroups', 'privateGroups', 'userGroups'));
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tạo nhóm.');
        }

        $user = Auth::user();
        // Kiểm tra điểm người dùng
        if ($user->totalpoint < self::REQUIRED_POINTS_TO_CREATE_GROUP) {
            return redirect()->route('group.index')->with('error', 'Bạn cần có ít nhất ' . self::REQUIRED_POINTS_TO_CREATE_GROUP . ' điểm để tạo nhóm.');
        }

        $groupTypes = GroupType::where('status', 'active')->get();
        return view('Tuongtac::frontend.group.create', compact('groupTypes'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tạo nhóm.');
        }

        $user = Auth::user();
        // Kiểm tra điểm người dùng
        if ($user->totalpoint < self::REQUIRED_POINTS_TO_CREATE_GROUP) {
            return redirect()->route('group.index')->with('error', 'Bạn cần có ít nhất ' . self::REQUIRED_POINTS_TO_CREATE_GROUP . ' điểm để tạo nhóm.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
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

        // Xử lý ảnh đại diện nhóm
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'group-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/group_photos', $filename);
            $group->photo = asset('storage/group_photos/' . $filename);
        } else {
            $group->photo = asset('backend/assets/dist/images/profile-6.jpg');
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

        // Kiểm tra quyền truy cập nếu là nhóm riêng tư
        if ($group->is_private == 1) {
            if (!Auth::check()) {
                return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để xem nhóm riêng tư.');
            }
            
            $userId = Auth::id();
            $members = json_decode($group->members ?? '[]', true);
            
            if (!in_array($userId, $members) && $group->author_id != $userId) {
                return redirect()->route('group.index')->with('error', 'Bạn không có quyền xem nhóm riêng tư này.');
            }
        }

        // Lấy thông tin đánh giá
        $this->addRatingData($group);

        // Lấy danh sách thành viên đã được duyệt
        $memberIds = json_decode($group->members ?? '[]', true);
        $members = User::whereIn('id', $memberIds)->get();
        
        // Lấy danh sách phó nhóm
        $moderatorIds = json_decode($group->moderators ?? '[]', true);
        $moderators = User::whereIn('id', $moderatorIds)->get();
        
        // Lấy danh sách bài viết của nhóm
        try {
            $posts = TBlog::with(['author'])
                          ->where('group_id', $id)
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

            foreach ($posts as $post) {
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
                if ($post->author) {
                    $post->user_url = route('front.userpages.viewuser', $post->author->id);
                }

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

        } catch (\Exception $e) {
            Log::error('Error fetching posts: ' . $e->getMessage());
            $posts = collect([]);
        }

        // Kiểm tra vai trò của người dùng trong nhóm
        $userRole = null;
        $isMember = false;
        $joinRequest = null;
        $isAdmin = false;
        $isModerator = false;

        if (Auth::check()) {
            $userId = Auth::id();
            $isAdmin = $userId == $group->author_id;
            $isModerator = in_array($userId, $moderatorIds);
            $isMember = in_array($userId, $memberIds) || $isAdmin;
            
            // Kiểm tra yêu cầu tham gia
            $pendingMembers = json_decode($group->pending_members ?? '[]', true);
            if (in_array($userId, $pendingMembers)) {
                $joinRequest = (object)['status' => 'pending'];
            }
            
            // Xác định vai trò
            if ($isAdmin) {
                $userRole = 'admin';
            } elseif ($isModerator) {
                $userRole = 'moderator';
            } elseif ($isMember) {
                $userRole = 'member';
            }
        }

        // Lấy danh sách yêu cầu tham gia
        $joinRequests = [];
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
            'isModerator'
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

        if (!Auth::check() || Auth::id() != $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền chỉnh sửa nhóm này.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_private' => 'nullable|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Kiểm tra xem title có thay đổi không
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

        // Xử lý ảnh đại diện nhóm
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'group-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/group_photos', $filename);
            $group->photo = asset('storage/group_photos/' . $filename);
        }

        $group->save();

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
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để yêu cầu tham gia nhóm.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra nếu người dùng đã là thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (in_array($userId, $members) || $userId == $group->author_id) {
            return redirect()->route('group.index')->with('info', 'Bạn đã là thành viên của nhóm này.');
        }

        // Kiểm tra nếu đã yêu cầu tham gia trước đó
        $pendingMembers = json_decode($group->pending_members ?? '[]', true);
        if (in_array($userId, $pendingMembers)) {
            return redirect()->route('group.index')->with('info', 'Bạn đã gửi yêu cầu tham gia nhóm và đang chờ duyệt.');
        }

        // Thêm vào danh sách chờ duyệt
        $pendingMembers[] = $userId;
        $group->pending_members = json_encode(array_values($pendingMembers));
        $group->save();

        return redirect()->route('group.index')->with('success', 'Yêu cầu tham gia nhóm đã được gửi và đang chờ duyệt.');
    }

    public function approveMember(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
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
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
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

    public function removeMember(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Kiểm tra quyền (chỉ admin hoặc moderator mới có quyền xóa thành viên)
        if ($userId != $group->author_id && !in_array($userId, json_decode($group->moderators ?? '[]', true))) {
            return redirect()->route('group.show', $id)->with('error', 'Bạn không có quyền xóa thành viên.');
        }

        // Kiểm tra user_id có tồn tại trong request
        if (!$request->has('user_id')) {
            return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        $memberUserId = $request->user_id;

        // Không thể xóa người tạo nhóm
        if ($memberUserId == $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Không thể xóa người tạo nhóm.');
        }

        // Moderator không thể xóa moderator khác
        $moderators = json_decode($group->moderators ?? '[]', true);
        if ($userId != $group->author_id && in_array($memberUserId, $moderators)) {
            return redirect()->route('group.show', $id)->with('error', 'Phó nhóm không thể xóa phó nhóm khác.');
        }

        // Cập nhật danh sách thành viên
        $members = json_decode($group->members ?? '[]', true);
        if (($key = array_search($memberUserId, $members)) !== false) {
            unset($members[$key]);
        }

        // Đồng thời xóa khỏi danh sách moderator nếu có
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

        return redirect()->route('group.show', $id)->with('success', 'Thành viên đã bị xóa khỏi nhóm.');
    }

    public function promoteModerator(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Chỉ admin mới có quyền phong phó nhóm
        if ($userId != $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Chỉ trưởng nhóm mới có quyền phong phó nhóm.');
        }

        // Kiểm tra user_id có tồn tại trong request
        if (!$request->has('user_id')) {
            return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        $memberUserId = $request->user_id;

        // Kiểm tra xem người được phong có phải là thành viên không
        $members = json_decode($group->members ?? '[]', true);
        if (!in_array($memberUserId, $members)) {
            return redirect()->route('group.show', $id)->with('error', 'Người dùng không phải là thành viên của nhóm.');
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

            return redirect()->route('group.show', $id)->with('success', 'Thành viên đã được phong làm phó nhóm.');
        }

        return redirect()->route('group.show', $id)->with('info', 'Thành viên này đã là phó nhóm.');
    }

    public function demoteModerator(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
        }

        $group = Group::findOrFail($id);
        $userId = Auth::id();

        // Chỉ admin mới có quyền hạ cấp phó nhóm
        if ($userId != $group->author_id) {
            return redirect()->route('group.show', $id)->with('error', 'Chỉ trưởng nhóm mới có quyền hạ cấp phó nhóm.');
        }

        // Kiểm tra user_id có tồn tại trong request
        if (!$request->has('user_id')) {
            return redirect()->route('group.show', $id)->with('error', 'Không tìm thấy thông tin người dùng.');
        }

        $memberUserId = $request->user_id;

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

            return redirect()->route('group.show', $id)->with('success', 'Phó nhóm đã được hạ cấp thành thành viên thường.');
        }

        return redirect()->route('group.show', $id)->with('error', 'Người dùng này không phải là phó nhóm.');
    }

    public function leaveGroup($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thực hiện chức năng này.');
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
            'group_id' => 'required|exists:groups,id',
        ]);

        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Bạn cần đăng nhập để đánh giá'], 401);
        }

        $userId = Auth::id();
        $point = $request->point;
        $itemId = $request->group_id;
        $itemCode = 'group';

        // Find vote record
        $voteRecord = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $itemId)
                        ->where('item_code', $itemCode)
                        ->first();

        if (!$voteRecord) {
            // Create new vote record if it doesn't exist
            $votes = [$userId => $point];
            $data = [
                'item_id' => $itemId,
                'item_code' => $itemCode,
                'count' => 1,
                'point' => $point,
                'votes' => json_encode($votes),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            \App\Modules\Tuongtac\Models\TVoteItem::create($data);
            
            return response()->json([
                'success' => true, 
                'averagePoint' => $point,
                'count' => 1
            ]);
        }

        // Update existing vote record
        $votes = json_decode($voteRecord->votes, true);

        // Update or add user's vote
        $votes[$userId] = $point;

        // Update count and average point
        $count = count($votes);
        $totalPoints = array_sum($votes);
        $averagePoint = $totalPoints / $count;

        $voteRecord->count = $count;
        $voteRecord->point = $averagePoint;
        $voteRecord->votes = json_encode($votes);
        $voteRecord->save();

        return response()->json([
            'success' => true, 
            'averagePoint' => $averagePoint,
            'count' => $count
        ]);
    }

    // Phương thức hỗ trợ để thêm dữ liệu đánh giá vào group
    private function addRatingData($group)
    {
        $voteItem = \App\Modules\Tuongtac\Models\TVoteItem::where('item_id', $group->id)
            ->where('item_code', 'group')
            ->first();

        $group->vote_count = $voteItem?->count ?? 0;
        $group->vote_average = $voteItem?->point ?? 0;

        // Nếu đăng nhập, lấy đánh giá của người dùng
        if (Auth::check() && $voteItem) {
            $votes = is_string($voteItem->votes) ? json_decode($voteItem->votes, true) : $voteItem->votes;
            $userId = Auth::id();
            $group->user_vote = $votes[$userId] ?? null;
        }

        return $group;
    }

    public function join($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tham gia nhóm.');
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
            return redirect()->route('group.show', $id)->with('info', 'Bạn đã gửi yêu cầu tham gia nhóm và đang chờ duyệt.');
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

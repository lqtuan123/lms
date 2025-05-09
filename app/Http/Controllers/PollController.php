<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PollController extends Controller
{
    /**
     * Hiển thị danh sách tất cả các khảo sát
     */
    public function index()
    {
        $activePolls = Poll::whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->latest()
            ->paginate(10, ['*'], 'active_page');
            
        $expiredPolls = Poll::where('expires_at', '<=', now())
            ->latest()
            ->paginate(10, ['*'], 'expired_page');
            
        $myPolls = Poll::where('created_by', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'my_page');
        
        return view('polls.index', compact('activePolls', 'expiredPolls', 'myPolls'));
    }

    /**
     * Hiển thị form tạo khảo sát mới
     */
    public function create()
    {
        // Nhóm có thể được lấy từ table groups nếu tồn tại
        // Vì không rõ cấu trúc của bảng groups nên tạm để trống
        $groups = collect(); // Gán mảng rỗng nếu không có bảng groups

        return view('polls.create', compact('groups'));
    }

    /**
     * Lưu khảo sát mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'question' => 'required',
            'options' => 'required|array|min:2|max:5',
            'options.*' => 'required|string|max:255',
            'group_id' => 'nullable|exists:groups,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $poll = Poll::create([
                'title' => $request->title,
                'question' => $request->question,
                'group_id' => $request->group_id,
                'expires_at' => $request->expires_at,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->options as $optionText) {
                PollOption::create([
                    'poll_id' => $poll->id,
                    'option_text' => $optionText,
                ]);
            }

            // Nếu có group_id và đang tạo khảo sát cho nhóm
            $blogCreated = false;
            if ($request->group_id) {
                // Có thể thêm mã để tích hợp khảo sát với bài viết group nếu cần
                try {
                    // Check if we need to create a blog post with poll
                    if (class_exists('\App\Modules\Tuongtac\Models\TBlog')) {
                        $blogModel = new \App\Modules\Tuongtac\Models\TBlog();
                        $blogModel->title = $request->title;
                        $blogModel->content = $request->question;
                        $blogModel->user_id = Auth::id();
                        $blogModel->status = 1;
                        $blogModel->group_id = $request->group_id;
                        
                        // Khởi tạo mảng votes với giá trị 0 cho mỗi option
                        $voteValues = array_fill(0, count($request->options), 0);
                        
                        // Create meta data for poll
                        $meta = [
                            'poll' => [
                                'id' => $poll->id,
                                'options' => $request->options,
                                'votes' => $voteValues
                            ]
                        ];
                        
                        $blogModel->meta = json_encode($meta);
                        $blogModel->save();
                        $blogCreated = true;
                        
                        Log::info('Đã tạo bài viết tích hợp khảo sát: ', [
                            'blog_id' => $blogModel->id,
                            'poll_id' => $poll->id,
                            'meta' => $meta
                        ]);
                    }
                } catch (\Exception $e) {
                    // Ignore the error if we can't create the blog post
                    // This keeps the poll creation working even if the blog integration fails
                    Log::error('Error creating blog post for poll: ' . $e->getMessage());
                }
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Khảo sát đã được tạo thành công!',
                    'poll' => [
                        'id' => $poll->id,
                        'title' => $poll->title,
                        'question' => $poll->question,
                        'url' => route('polls.show', $poll->id),
                        'blog_created' => $blogCreated
                    ]
                ]);
            }

            return redirect()->route('polls.show', $poll->id)
                ->with('status', 'Khảo sát đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi tạo khảo sát: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi khi tạo khảo sát: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi tạo khảo sát: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị một khảo sát
     */
    public function show(Poll $poll)
    {
        $hasVoted = $poll->hasUserVoted(Auth::id());
        $userVote = null;

        if ($hasVoted) {
            $userVote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('polls.show', compact('poll', 'hasVoted', 'userVote'));
    }
    
    /**
     * Lấy danh sách người đã bình chọn khảo sát
     */
    public function getVoters(Poll $poll)
    {
        // Kiểm tra xem người dùng có quyền xem danh sách bình chọn không
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn cần đăng nhập để xem danh sách người bình chọn.'
            ], 401);
        }
        
        try {
            // Lấy danh sách người bình chọn kèm thông tin bình chọn
            $votes = PollVote::where('poll_id', $poll->id)
                ->with(['user', 'option'])
                ->get();
                
            $voters = [];
            
            foreach ($votes as $vote) {
                if ($vote->user) {
                    $voters[] = [
                        'id' => $vote->user->id,
                        'name' => $vote->user->name,
                        'photo' => $vote->user->photo ?? null,
                        'option_id' => $vote->option_id,
                        'option_text' => $vote->option->option_text ?? 'N/A',
                        'voted_at' => $vote->created_at->diffForHumans()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'voters' => $voters
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy danh sách người bình chọn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý bình chọn - Sửa lại để hoạt động với API tốt hơn
     */
    public function vote(Request $request, Poll $poll)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để bình chọn.'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để bình chọn.');
        }
        
        // Kiểm tra xem khảo sát đã hết hạn chưa
        if ($poll->isExpired()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khảo sát này đã kết thúc.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Khảo sát này đã kết thúc.');
        }
        
        // Kiểm tra xem người dùng đã bình chọn chưa
        if ($poll->hasUserVoted(Auth::id())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã bình chọn cho khảo sát này rồi.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Bạn đã bình chọn cho khảo sát này rồi.');
        }
        
        // Xác thực dữ liệu
        $validator = Validator::make($request->all(), [
            'option_id' => 'required|exists:poll_options,id',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Kiểm tra xem option có thuộc về poll này không
        $optionBelongsToPoll = PollOption::where('id', $request->option_id)
            ->where('poll_id', $poll->id)
            ->exists();
            
        if (!$optionBelongsToPoll) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lựa chọn không hợp lệ.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Lựa chọn không hợp lệ.');
        }
        
        try {
            DB::beginTransaction();
            
            // Lưu bình chọn
            PollVote::create([
                'poll_id' => $poll->id,
                'option_id' => $request->option_id,
                'user_id' => Auth::id(),
            ]);
            
            // Cộng điểm khi tham gia khảo sát
            if (Auth::check()) {
                Auth::user()->addPoint('vote_poll', $poll->id, 'App\Models\Poll');
            }
            
            DB::commit();
            
            // Nếu là ajax request, trả về kết quả bình chọn
            if ($request->ajax() || $request->wantsJson()) {
                // Lấy kết quả mới nhất sau khi bình chọn
                $results = $poll->getVotesCountByOption();
                $totalVotes = $poll->getTotalVotesCount();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Bình chọn của bạn đã được ghi nhận!',
                    'results' => [
                        'options' => array_column($results, 'text'),
                        'counts' => array_column($results, 'count'),
                        'percentages' => array_column($results, 'percentage'),
                        'total_votes' => $totalVotes
                    ],
                    'voted_option_id' => $request->option_id
                ]);
            }
            
            return redirect()->back()->with('status', 'Bình chọn của bạn đã được ghi nhận!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi bình chọn: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi khi bình chọn: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi bình chọn: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa khảo sát
     */
    public function edit(Poll $poll)
    {
        // Kiểm tra quyền
        if (Auth::id() !== $poll->created_by) {
            return redirect()->route('polls.show', $poll->id)
                ->with('error', 'Bạn không có quyền chỉnh sửa khảo sát này.');
        }
        
        // Nhóm có thể được lấy từ table groups nếu tồn tại
        // Vì không rõ cấu trúc của bảng groups nên tạm để trống
        $groups = collect(); // Gán mảng rỗng nếu không có bảng groups
        
        return view('polls.edit', compact('poll', 'groups'));
    }

    /**
     * Cập nhật khảo sát
     */
    public function update(Request $request, Poll $poll)
    {
        // Kiểm tra quyền
        if (Auth::id() !== $poll->created_by) {
            return redirect()->route('polls.show', $poll->id)
                ->with('error', 'Bạn không có quyền chỉnh sửa khảo sát này.');
        }
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'question' => 'required',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
            'option_ids' => 'required|array|min:2',
            'option_ids.*' => 'required|exists:poll_options,id',
            'group_id' => 'nullable|exists:groups,id',
            'expires_at' => 'nullable|date',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            $poll->update([
                'title' => $request->title,
                'question' => $request->question,
                'group_id' => $request->group_id,
                'expires_at' => $request->expires_at,
            ]);
            
            // Cập nhật các lựa chọn
            foreach ($request->option_ids as $key => $optionId) {
                PollOption::where('id', $optionId)->update([
                    'option_text' => $request->options[$key]
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('polls.show', $poll->id)
                ->with('status', 'Khảo sát đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi cập nhật khảo sát: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Xóa khảo sát
     */
    public function destroy(Poll $poll)
    {
        // Kiểm tra quyền
        if (Auth::id() !== $poll->created_by) {
            return redirect()->route('polls.show', $poll->id)
                ->with('error', 'Bạn không có quyền xóa khảo sát này.');
        }
        
        try {
            $poll->delete();
            return redirect()->route('polls.index')
                ->with('status', 'Khảo sát đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi xóa khảo sát: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý bình chọn qua AJAX
     */
    public function ajaxVote(Request $request, $pollId)
    {
        $poll = Poll::findOrFail($pollId);
        
        // Debug: Log request data
        Log::info('AJAX Vote Request:', [
            'poll_id' => $pollId,
            'option_index' => $request->option_index,
            'option_id' => $request->option_id,
            'user_id' => Auth::id()
        ]);
        
        // Kiểm tra xem khảo sát đã hết hạn chưa
        if ($poll->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Khảo sát này đã kết thúc.'
            ], 400);
        }
        
        // Kiểm tra xem người dùng đã bình chọn chưa
        if ($poll->hasUserVoted(Auth::id())) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã bình chọn cho khảo sát này rồi.'
            ], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'option_index' => 'required|integer|min:0',
            'option_id' => 'nullable|exists:poll_options,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Lấy ID của option
        $optionId = $request->option_id;
        
        // Nếu không có option_id, sử dụng option_index để lấy option từ poll
        if (!$optionId) {
            $options = $poll->options;
            if ($request->option_index >= count($options)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lựa chọn không hợp lệ.'
                ], 400);
            }
            $selectedOption = $options[$request->option_index];
            $optionId = $selectedOption->id;
        }
        
        // Kiểm tra xem option có thuộc về poll này không
        $optionBelongsToPoll = PollOption::where('id', $optionId)
            ->where('poll_id', $poll->id)
            ->exists();
            
        if (!$optionBelongsToPoll) {
            return response()->json([
                'success' => false,
                'message' => 'Lựa chọn không hợp lệ.'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Lưu bình chọn vào database
            PollVote::create([
                'poll_id' => $poll->id,
                'option_id' => $optionId,
                'user_id' => Auth::id(),
            ]);
            
            // Cập nhật meta dữ liệu trong bài viết (nếu có)
            try {
                // Tìm bài viết chứa khảo sát này
                $post = \App\Modules\Tuongtac\Models\TBlog::where('meta', 'like', '%"id":"' . $poll->id . '"%')
                    ->orWhere('meta', 'like', '%"id":' . $poll->id . '%')
                    ->first();
                    
                if ($post) {
                    $meta = json_decode($post->meta, true);
                    
                    if (isset($meta['poll']) && isset($meta['poll']['votes'])) {
                        // Tăng số lượng vote cho lựa chọn
                        $meta['poll']['votes'][$request->option_index] += 1;
                        $post->meta = json_encode($meta);
                        $post->save();
                    }
                }
            } catch (\Exception $e) {
                // Ghi log lỗi nhưng không dừng transaction
                Log::error('Error updating blog meta data for poll: ' . $e->getMessage());
            }
            
            DB::commit();
            
            // Lấy kết quả mới nhất sau khi bình chọn
            $results = $poll->getVotesCountByOption();
            $totalVotes = $poll->getTotalVotesCount();
            
            // Debug: Log response data 
            Log::info('AJAX Vote Response:', [
                'poll_id' => $pollId,
                'total_votes' => $totalVotes,
                'options_count' => count($results),
                'first_option' => !empty($results) ? [
                    'text' => $results[0]['text'],
                    'count' => $results[0]['count'],
                    'percentage' => $results[0]['percentage']
                ] : null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bình chọn của bạn đã được ghi nhận!',
                'results' => [
                    'options' => array_column($results, 'text'),
                    'counts' => array_column($results, 'count'),
                    'percentages' => array_column($results, 'percentage'),
                    'total_votes' => $totalVotes
                ],
                'voted_option_id' => $optionId,
                'option_index' => $request->option_index
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in ajaxVote: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi bình chọn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý thay đổi bình chọn khảo sát
     */
    public function changeVote(Request $request, Poll $poll)
    {
        // Debug: Log request data
        Log::info('Change Vote Request:', [
            'poll_id' => $poll->id,
            'option_id' => $request->option_id,
            'option_index' => $request->option_index,
            'user_id' => Auth::id()
        ]);
        
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn cần đăng nhập để thay đổi bình chọn.'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để thay đổi bình chọn.');
        }
        
        // Kiểm tra xem khảo sát đã hết hạn chưa
        if ($poll->isExpired()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khảo sát này đã kết thúc.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Khảo sát này đã kết thúc.');
        }
        
        // Kiểm tra xem người dùng đã bình chọn chưa
        if (!$poll->hasUserVoted(Auth::id())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa bình chọn cho khảo sát này.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Bạn chưa bình chọn cho khảo sát này.');
        }
        
        // Xác thực dữ liệu
        $validator = Validator::make($request->all(), [
            'option_id' => 'required|exists:poll_options,id',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Kiểm tra xem option có thuộc về poll này không
        $optionBelongsToPoll = PollOption::where('id', $request->option_id)
            ->where('poll_id', $poll->id)
            ->exists();
            
        if (!$optionBelongsToPoll) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lựa chọn không hợp lệ.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Lựa chọn không hợp lệ.');
        }
        
        try {
            DB::beginTransaction();
            
            // Tìm và cập nhật bình chọn hiện tại
            $vote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', Auth::id())
                ->first();
                
            if ($vote) {
                // Không cần cập nhật nếu lựa chọn không thay đổi
                if ($vote->option_id == $request->option_id) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Bạn đã chọn lựa chọn này rồi.',
                            'results' => [
                                'options' => array_column($poll->getVotesCountByOption(), 'text'),
                                'counts' => array_column($poll->getVotesCountByOption(), 'count'),
                                'percentages' => array_column($poll->getVotesCountByOption(), 'percentage'),
                                'total_votes' => $poll->getTotalVotesCount()
                            ],
                            'no_change' => true
                        ]);
                    }
                    return redirect()->back()->with('info', 'Bạn đã chọn lựa chọn này rồi.');
                }
                
                // Cập nhật bình chọn
                $vote->option_id = $request->option_id;
                $vote->updated_at = now();
                $vote->save();
                
                // Cập nhật meta dữ liệu trong bài viết (nếu có)
                try {
                    // Tìm bài viết chứa khảo sát này
                    $post = \App\Modules\Tuongtac\Models\TBlog::where('meta', 'like', '%"id":"' . $poll->id . '"%')
                        ->orWhere('meta', 'like', '%"id":' . $poll->id . '%')
                        ->first();
                        
                    if ($post) {
                        $meta = json_decode($post->meta, true);
                        
                        if (isset($meta['poll']) && isset($meta['poll']['votes'])) {
                            // TODO: Cập nhật số lượng vote cho lựa chọn trong meta
                            // Logic phức tạp hơn vì cần giảm số vote của option cũ và tăng số vote của option mới
                        }
                    }
                } catch (\Exception $e) {
                    // Ghi log lỗi nhưng không dừng transaction
                    Log::error('Error updating blog meta data for poll when changing vote: ' . $e->getMessage());
                }
            }
            
            DB::commit();
            
            // Nếu là ajax request, trả về kết quả bình chọn
            if ($request->ajax() || $request->wantsJson()) {
                // Lấy kết quả mới nhất sau khi bình chọn
                $results = $poll->getVotesCountByOption();
                $totalVotes = $poll->getTotalVotesCount();
                
                // Debug: Log response data
                Log::info('Change Vote Response:', [
                    'poll_id' => $poll->id,
                    'total_votes' => $totalVotes,
                    'options_count' => count($results),
                    'first_option' => !empty($results) ? [
                        'text' => $results[0]['text'],
                        'count' => $results[0]['count'],
                        'percentage' => $results[0]['percentage']
                    ] : null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Bình chọn của bạn đã được cập nhật!',
                    'results' => [
                        'options' => array_column($results, 'text'),
                        'counts' => array_column($results, 'count'),
                        'percentages' => array_column($results, 'percentage'),
                        'total_votes' => $totalVotes
                    ],
                    'voted_option_id' => $request->option_id,
                    'option_index' => $request->option_index ?? -1
                ]);
            }
            
            return redirect()->back()->with('status', 'Bình chọn của bạn đã được cập nhật!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in changeVote: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi khi thay đổi bình chọn: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi thay đổi bình chọn: ' . $e->getMessage());
        }
    }
}

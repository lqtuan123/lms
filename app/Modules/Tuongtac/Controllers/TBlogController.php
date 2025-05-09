<?php

namespace App\Modules\Tuongtac\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TPage;
use  App\Modules\Tuongtac\Models\TPageItem;
use  App\Modules\Group\Models\Group;
use  App\Modules\GroupMember\Models\GroupMember;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Modules\Resource\Models\Resource;
use App\Modules\Resource\Models\ResourceType;
use App\Modules\Tuongtac\Models\TComment;
use App\Modules\Tuongtac\Models\TMotionItem;
use Illuminate\Support\Facades\Auth;
use App\Modules\Tuongtac\Models\TMotion;

class TBlogController extends Controller
{
    public function getPostContent($slug)
    {
        $post = TBlog::where('slug', $slug)->first();

        if (!$post) {
            return response()->json(['error' => 'Bài viết không tồn tại'], 404);
        }
        $adsense_code = '<ins class="adsbygoogle"
            style="display:block; text-align:center;"
            data-ad-layout="in-article"
            data-ad-format="fluid"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="3375673265"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';

        $content = $post->content;
        // Tìm vị trí của thẻ <p> đầu tiên
        $position = strpos($content, '</p>', strlen($content) / 2); // Sau thẻ </p> gần giữa

        // Nếu tìm thấy vị trí, chèn mã AdSense
        if ($position !== false) {
            $new_content = substr_replace($content, $adsense_code, $position + 4, 0); // +4 vì thêm sau </p>
        } else {
            // Nếu không có <p>, chèn vào giữa
            $new_content = $content . $adsense_code;
        }
        return response()->json([
            'title' => $post->title,
            'content' => $new_content,
        ]);
    }
    public function myblog()
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);

        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        ////
        $data['pagetitle'] = "Cộng đồng itcctv - bài viết của tôi";
        $data['pagebreadcrumb'] = '<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';

        $data['pagebreadcrumb'] .= '  </ol> </nav>';
        $data['page_up_title'] = "Cộng đồng itcctv - bài viết của tôi";
        $data['page_subtitle'] = "Cộng đồng itcctv - bài viết của tôi";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Cộng đồng itcctv - bài viết của tôi";

        $data['posts']   = TBlog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc') // Sắp xếp theo thời gian tạo mới nhất
            ->paginate(30);

        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog) {

            $blog->user_url = route('front.user.profile', $blog->user_id);
            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            $blog->comment_count = TComment::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->where('status', 'active')
                ->count();

            $motionItem = TMotionItem::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->first();

            $blog->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
            
            // Kiểm tra xem người dùng đã yêu thích bài viết chưa
            $blog->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($blog->id, 'tblog');
        }
        return view('Tuongtac::frontend.blogs.index', $data);
    }
    public function show($slug)
    {
        $tblog = TBlog::with('group')->where('slug', $slug)->first();
        $resid = $tblog?->resources;

        if (!$tblog) {
            return back()->with('error', 'Không tìm thấy dữ liệu');
        }

        // Kiểm tra quyền truy cập nếu bài viết thuộc về nhóm riêng tư
        if ($tblog->group_id && $tblog->group && $tblog->group->is_private) {
            $user = auth()->user();
            $userId = auth()->id();
            
            // Nếu chưa đăng nhập hoặc không phải thành viên nhóm
            if (!$userId || 
                !($tblog->group->isMember($userId) || 
                  $tblog->group->isAdmin($userId) || 
                  $tblog->group->isModerator($userId))) {
                return redirect()->route('front.tblogs.index')
                    ->with('error', 'Bạn không có quyền xem bài viết này vì nó thuộc nhóm riêng tư');
            }
        }

        $data['detail'] = \App\Models\SettingDetail::find(1);

        $user  = auth()->user();
        ////
        $data['pagetitle'] = "" . $tblog->title;
        $data['page_up_title'] = "" . $tblog->title;;
        $data['page_subtitle'] = "" . $tblog->title;;
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "" . $tblog->title;

        // Thêm thông tin của nhóm nếu bài viết thuộc nhóm
        if ($tblog->group_id && $tblog->group) {
            $data['group'] = $tblog->group;
            $data['page_subtitle'] .= " · Nhóm: " . $tblog->group->title;
        }

        $data['menutags'] = TTag::where('slug', '<>', 'rao-vat')->orderBy('hit')->limit(10)->get();

        $data['item_code'] = 'tblog';
        $data['post'] =  $tblog;
        $data['post']->tags = \DB::table('t_tags')
            ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
            ->where('t_tag_items.item_id', $tblog->id)
            ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
            ->select('t_tags.*')
            ->get();
            
        // Tính số lượng bình luận
        $data['post']->comment_count = TComment::where('item_id', $data['post']->id)
            ->where('item_code', 'tblog')
            ->where('status', 'active')
            ->count();

        // Lấy thông tin tương tác (like, love, v.v.)
        $motionItem = TMotionItem::where('item_id', $data['post']->id)
            ->where('item_code', 'tblog')
            ->first();

        // Tính tổng số lượt thích
        $data['post']->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;

        // Kiểm tra xem người dùng đã bookmark bài viết chưa
        $data['post']->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($data['post']->id, 'tblog');

        $data['keyword'] = "";
        foreach ($data['post']->tags  as $tag) {
            $data['keyword'] .= $tag->title . ",";
        }
        $data['description'] =  $data['keyword'];
        if ($data['post']->photo && $data['post']->photo != '') {
            $photos = json_decode($data['post']->photo, true); // Thêm `true` để decode thành mảng.
            if (is_array($photos) && count($photos) > 0) {
                $data['ogimage'] = $photos[0];
            }
        }

        $data['post']->author = \App\Models\User::find($tblog->user_id);
        $data['post']->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($tblog->id, 'tblog');
        $data['post']->user_url = route('front.user.profile', $tblog->user_id);
        
        $data['post']->user_has_liked = isset(auth()->user()->id) ? TMotion::checkUserReacted($data['post']->id, 'tblog', auth()->user()->id) : false;
        $data['post']->is_bookmarked = isset(auth()->user()->id) ? \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($data['post']->id, 'tblog') : false;
        
        return view('Tuongtac::frontend.blogs.show', $data);
    }
    public function favblog()
    {
        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);


        ////
        $data['pagetitle'] = "Cộng đồng itcctv - Yêu thích";
        $data['pagebreadcrumb'] = '<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';

        $data['pagebreadcrumb'] .= '  </ol> </nav>';
        $data['page_up_title'] = "Cộng đồng itcctv - Yêu thích";
        $data['page_subtitle'] = "Cộng đồng itcctv - Yêu thích";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Cộng đồng itcctv - Yêu thích";


        // $data['posts'] = \DB::table('t_blogs')
        //     ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
        //     ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
        //     ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
        //     ->select('t_blogs.*')
        //     ->paginate(10);
        $userId = auth()->id(); // Hoặc lấy từ request/session tùy vào logic của bạn

        $data['posts'] = \DB::table('t_blogs')
            ->select('t_blogs.*', 't_recommends.created_at as recommend_date')
            ->join('t_recommends', function ($join) use ($userId) {
                $join->on('t_blogs.id', '=', 't_recommends.item_id')
                    ->where('t_recommends.item_code', 'tblog')
                    ->where('t_recommends.user_id', $userId);
            })
            ->where('t_blogs.status', '1') // Chỉ lấy bài viết có status = 1
            ->orderBy('recommend_date', 'desc') // Sắp xếp theo thời gian yêu thích mới nhất
            ->paginate(30);
        // $posts = Post::whereHas('tags', function ($query) use ($tagName) {
        //     $query->where('name', $tagName);
        // })->paginate(10);

        $data['menutags'] = TTag::where('slug', '<>', 'rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog) {
            $blog->user_url = route('front.user.profile', $blog->user_id);

            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }

            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            $blog->comment_count = TComment::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->where('status', 'active')
                ->count();

            $motionItem = TMotionItem::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->first();

            $blog->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
            
            // Kiểm tra xem người dùng đã yêu thích bài viết chưa
            $blog->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($blog->id, 'tblog');
        }
        return view('Tuongtac::frontend.blogs.index', $data);
    }
    public function trendblog()
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);
        //$data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
        ////
        $data['pagetitle'] = "Cộng đồng itcctv ";
        $data['pagebreadcrumb'] = '<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';

        $data['pagebreadcrumb'] .= '  </ol> </nav>';
        $data['page_up_title'] = 'Cộng đồng itcctv';
        $data['page_subtitle'] = "Cộng đồng itcctv";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Cộng đồng itcctv";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Cộng đồng itcctv ";


        $data['posts'] = TBlog::select('t_blogs.*', \DB::raw("
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Like')), 0) +
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Love')), 0) +
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Sad')), 0) +
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Wow')), 0) +
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Haha')), 0) -
        COALESCE(JSON_UNQUOTE(JSON_EXTRACT(t_motion_items.motions, '$.Angry')), 0) 
         AS total_interactions
        
    "))
            ->leftJoin('t_motion_items', function ($join) {
                $join->on('t_blogs.id', '=', 't_motion_items.item_id')
                    ->where('t_motion_items.item_code', 'tblog');
            })

            ->where('t_blogs.status', 1)
            ->orderByDesc('total_interactions') // Sắp xếp theo tổng số lượt tương tác (nhiều nhất lên đầu)
            ->paginate(30);


        $data['menutags'] = TTag::where('slug', '<>', 'rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog) {
            $blog->user_url = route('front.user.profile', $blog->user_id);

            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            $blog->comment_count = TComment::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->where('status', 'active')
                ->count();

            $motionItem = TMotionItem::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->first();

            $blog->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
            
            // Kiểm tra xem người dùng đã yêu thích bài viết chưa
            $blog->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($blog->id, 'tblog');
        }
        return view('Tuongtac::frontend.blogs.index', $data);
    }

    public function tag(Request $request, $tag)
    {
        $tag = TTag::where('slug', $tag)->first();
        if (!$tag) {
            return back()->with('error', 'Không tìm thấy dữ liệu');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);

        $user  = auth()->user();
        ////
        $data['pagetitle'] = "Cộng đồng itcctv - bài viết " . $tag->title;
        $data['pagebreadcrumb'] = '<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';

        $data['pagebreadcrumb'] .= '  </ol> </nav>';
        $data['page_up_title'] = "Cộng đồng itcctv - bài viết " . $tag->title;;
        $data['page_subtitle'] = "Cộng đồng itcctv - bài viết " . $tag->title;;
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Cộng đồng itcctv - bài viết " . $tag->title;



        // $data['posts'] = \DB::table('t_blogs')
        //     ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
        //     ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
        //     ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
        //     ->where('t_blogs.status',1)
        //     ->select('t_blogs.*')
        //     ->orderBy('t_blogs.id','desc')
        //     ->paginate(40);

        $data['posts'] = \DB::table('t_blogs')
            ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
            ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
            ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
            ->where('t_blogs.status', 1)
            ->select('t_blogs.*')
            ->orderBy('t_blogs.created_at', 'desc') // Sắp xếp theo thời gian tạo mới nhất
            ->paginate(30);

        $data['menutags'] = TTag::where('slug', '<>', 'rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog) {
            $blog->user_url = route('front.user.profile', $blog->user_id);

            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }

            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);

            $blog->comment_count = TComment::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->where('status', 'active')
                ->count();

            $motionItem = TMotionItem::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->first();

            $blog->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            
            // Kiểm tra xem người dùng đã yêu thích bài viết chưa
            $blog->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($blog->id, 'tblog');
        }
        return view('Tuongtac::frontend.blogs.index', $data);
    }
    public function index(Request $request)
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $user  = auth()->user();

        $data['pagetitle'] = "Cộng đồng itcctv ";
        $data['pagebreadcrumb'] = '<nav aria-label="breadcrumb" class="theme-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="">Trang chủ</a></li>
            </ol> 
        </nav>';
        $data['page_up_title'] = 'Cộng đồng itcctv';
        $data['page_subtitle'] = "Cộng đồng itcctv";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Cộng đồng itcctv";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Cộng đồng itcctv ";

        $search = $request->input('search'); // Lấy giá trị tìm kiếm từ form

        $userId = auth()->id();

        // Lấy danh sách bài viết phù hợp với điều kiện hiển thị
        $query = TBlog::with('group') // Nạp sẵn thông tin nhóm để tránh N+1 query
            ->when($search, function ($query, $search) {
                return $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%");
            })
            ->where('status', 1)
            ->where(function($query) use ($userId) {
                // Bài viết không thuộc nhóm nào
                $query->where(function($q) {
                    $q->whereNull('group_id')
                      ->orWhere('group_id', 0);
                });
                
                
                // HOẶC bài viết thuộc nhóm công khai
                $query->orWhereHas('group', function($q) {
                    $q->where('is_private', 0)->where('status', 'active');
                });
                
                // HOẶC bài viết thuộc nhóm riêng tư nhưng người dùng là thành viên (nếu đã đăng nhập)
                if ($userId) {
                    $query->orWhereHas('group', function($q) use ($userId) {
                        $q->where('is_private', 1)
                          ->where('status', 'active')
                          ->where(function($subQ) use ($userId) {
                              // Người dùng là thành viên
                              $subQ->whereRaw("JSON_CONTAINS(members, '\"$userId\"')");
                              // Hoặc là người tạo nhóm
                              $subQ->orWhere('author_id', $userId);
                              // Hoặc là phó nhóm
                              $subQ->orWhereRaw("JSON_CONTAINS(moderators, '\"$userId\"')");
                          });
                    });
                }
            })
            ->orderBy('created_at', 'desc') // Sắp xếp theo thời gian tạo mới nhất
            ->paginate(15);

        $data['posts'] = $query;
        $data['menutags'] = TTag::where('slug', '<>', 'rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';

        foreach ($data['posts'] as $blog) {
            $blog->user_url = route('front.user.profile', $blog->user_id);
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            $blog->comment_count = TComment::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->where('status', 'active')
                ->count();

            $motionItem = TMotionItem::where('item_id', $blog->id)
                ->where('item_code', 'tblog')
                ->first();

            $blog->likes_count = $motionItem ? $motionItem->getTotalReactionsCount() : 0;
            
            // Thêm thông tin nhóm nếu bài viết thuộc về một nhóm
            if ($blog->group_id && $blog->group) {
                $blog->group_url = route('group.show', $blog->group_id);
                $blog->group_name = $blog->group->title;
            }

            // Kiểm tra xem người dùng đã bookmark bài viết chưa
            $blog->is_bookmarked = \App\Modules\Tuongtac\Models\TRecommend::hasBookmarked($blog->id, 'tblog');
        }

        return view('Tuongtac::frontend.blogs.index', $data);
    }

    public function addpageblog($id, $group_id = null)
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['tags'] = \App\Modules\Tuongtac\Models\TTag::orderBy('title', 'asc')->get();
        $data['toptags'] = \App\Modules\Tuongtac\Models\TTag::orderBy('hit', 'desc')->limit(15)->get();
        $data['page_id'] = $id;
        
        // Truyền group_id vào view nếu có
        if ($group_id !== null) {
            $data['group_id'] = $group_id;
        }
        
        return view('Tuongtac::frontend.blogs.create', $data);
    }
    public function check_quyendangbai($page)
    {
        $user = auth()->user();
        if (!$user)
            return 0;
        $is_create = 0;
        if ($page->item_code == 'group') {
            $group = Group::find($page->item_id);
            if (! $group) {
                return 0;
            } else {
                // dd($group,$group->getRole($user->id));
                if ($user &&  $group->getRole($user->id) != '') {
                    $data['role'] = $group->getRole($user->id);
                    $is_create = 1;
                }
            }
        }
        if ($page->item_code == 'user') {
            if ($page->item_id == auth()->id()) {
                $is_create = 1;
            }
        }
        if ($user->role == 'admin') {
            $is_create = 1;
        }
        return $is_create;
    }
    public function addgroupblog($id)
    {
        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        $page = TPage::find($id);
        if (!$page) {
            return redirect()->back()->with('error', 'Không tìm thấy trang!');
        }
        $is_create = $this->check_quyendangbai($page);

        return $this->addpageblog($page->id);
    }
    public function create()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        
        // Lấy tham số referrer từ query string để biết người dùng đến từ đâu
        $referrer = request()->query('ref');
        
        // Mặc định group_id = 0 nếu người dùng đến từ trang profile
        $group_id = 0;
        
        $page = TPage::where('item_id', $user->id)->where('item_code', 'user')->first();
        if (!$page) {
            $slug = Str::slug($user->full_name);
            $ppage = TPage::where('slug', $slug)->first();
            if ($ppage) {
                $slug .= uniqid();
            }
            $data['item_id'] = $user->id;
            $data['item_code'] = 'user';
            $data['title'] = $user->full_name;
            $data['slug'] = $slug;
            $data['description'] = "";
            $data['banner'] = "https://itcctv.vn/images/profile-8.jpg";
            $data['avatar'] = $user->photo ? $user->photo : "https://itcctv.vn/images/profile-8.jpg";
            $data['status'] = "active";
            $page = TPage::create($data);
        }
        
        // Truyền giá trị group_id vào view để form sử dụng
        return $this->addpageblog($page->id, $group_id);
    }
    public function store(Request $request)
    {
        // dd(request()->all());
        $user = auth()->user();
        if (!$user) {
            if ($request->ajax() || $request->header('X-Requested-With')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('front.login');
        }
        
        // Xác thực dữ liệu
        $validator = \Validator::make($request->all(), [
            'title' => 'required|max:255',
            'content' => 'required',
            'photo' => 'nullable',
            'tags' => 'nullable|array', // Tags phải là một mảng
            'urls' => 'nullable|array',
            'document' => 'nullable|array',
            'page_id' => 'nullable|integer',
            'group_id' => 'nullable|integer', // Thêm validation cho group_id
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:20480',
        ]);
        
        \Illuminate\Support\Facades\Log::info('Photo data in store request:', [
            'photo' => $request->photo,
            'has_photo' => $request->has('photo'),
            'all_data' => $request->all()
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax() || $request->header('X-Requested-With')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Tạo slug từ tiêu đề
        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        while (TBlog::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Tạo dữ liệu cho bài viết
        $data = [
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'user_id' => $user->id,
            'hit' => 0,
            'status' => $request->has('status') ? $request->status : 1, // Mặc định là hiển thị
            'group_id' => $request->group_id, // Lưu group_id
        ];

        // Xử lý ảnh từ Dropzone
        if ($request->photo) {
            $data['photo'] = $request->photo;
        }

        // Lưu bài viết vào database
        $post = TBlog::create($data);

        // Xử lý tags
        $tag_ids = $request->tags;
        $tagcontroller = new \App\Modules\Tuongtac\Controllers\TTagController();
        $tagcontroller->store_item_tag($post->id, $tag_ids, 'tblog');

        // Thêm điểm cho người dùng
        if (Auth::check()) {
            Auth::user()->addPoint('create_blog', $post->id, 'App\Modules\Tuongtac\Models\TBlog');
        }

        // Xử lý tài liệu đính kèm
        $resourceIds = array();
        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $resource = Resource::createResource($request, $file, 'TBlog');
                // Đảm bảo file được lưu dưới dạng file, không phải URL
                $resource->link_code = 'file';
                $resource->save();
                $resourceIds[] = $resource->id;
            }
        }

        // Lưu lại URL các file đã upload để không tạo trùng lặp
        $uploadedFileUrls = [];
        if (!empty($resourceIds)) {
            $uploadedResources = Resource::whereIn('id', $resourceIds)->get();
            foreach ($uploadedResources as $res) {
                $uploadedFileUrls[] = $res->url;
            }
        }

        // Xử lý URLs
        $urls = $request->urls;
        if ($urls) {
            foreach ($urls as $url) {
                if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                    // Bỏ qua URLs trỏ đến các file đã upload để tránh trùng lặp
                    if (in_array($url, $uploadedFileUrls)) {
                        continue;
                    }
                    
                    // Kiểm tra xem URL đã tồn tại trong resources chưa
                    $exists = false;
                    foreach ($resourceIds as $resId) {
                        $existingResource = Resource::find($resId);
                        if ($existingResource && $existingResource->url == $url) {
                            $exists = true;
                            break;
                        }
                    }
                    
                    // Chỉ thêm URL mới nếu nó chưa tồn tại
                    if (!$exists) {
                        // Xác định nếu URL là YouTube để đặt tiêu đề phù hợp
                        $urlTitle = Resource::getYouTubeID($url) ? "YouTube Video" : "Liên kết web";
                        $resourceIds[] = Resource::createUrlResource($urlTitle, $url, 'other', 'tblog')->id;
                    }
                }
            }
        }

        // Lưu thông tin tài nguyên
        $post->resources = $resourceIds;
        $post->save();

        // Xử lý page_id nếu có
        if ($request->page_id) {
            $page = TPage::find($request->page_id);
            if ($page) {
                if ($this->check_quyendangbai($page)) {
                    $datam['item_id'] = $post->id;
                    $datam['item_code'] = 'tblog';
                    $datam['page_id'] = $page->id;
                    $item = TPageItem::create($datam);
                    $item->order_id = $item->id;
                    $item->save();
                }
            }
        }

        // Trả về response dựa trên loại request
        if ($request->ajax() || $request->header('X-Requested-With')) {
            return response()->json([
                'success' => true,
                'message' => 'Bài viết đã được thêm thành công!',
                'post' => [
                    'id' => $post->id,
                    'slug' => $post->slug,
                    'title' => $post->title
                ]
            ]);
        }

        // Redirect dựa trên điều kiện
        if (isset($page)) {
            return redirect()->route('group.show', $page->item_id)->with('success', 'Bài viết đã được thêm thành công!');
        } elseif ($request->group_id) {
            // Nếu bài viết thuộc về nhóm, chuyển hướng đến trang chi tiết nhóm
            return redirect()->route('group.show', $request->group_id)->with('success', 'Bài viết đã được thêm thành công!');
        }

        return redirect()->route('front.tblogs.index')->with('success', 'Bài viết đã được thêm thành công!');
    }
    public function status($id)
    {
        $tblog = TBlog::findOrFail($id);
        if ($tblog->user_id != auth()->id()) {
            return back()->with('error', 'Không có quyền thay đổi!');
        } else {
            $tblog->status = ($tblog->status + 1) % 2;
            $tblog->save();
            return back()->with('success', 'đã cập nhật thành công!');
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        if (!$user) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Vui lòng đăng nhập để thực hiện chức năng này'], 401);
            }
            return redirect()->route('front.login');
        }

        $post = TBlog::with(['author', 'tags'])->findOrFail($id);

        // Kiểm tra quyền chỉnh sửa
        if ($post->user_id != $user->id && !($post->user_id == 0 && $user)) {
            if (request()->ajax()) {
                return response()->json(['error' => 'Bạn không có quyền chỉnh sửa bài viết này'], 403);
            }
            return redirect()->back()->with(['flash_level' => 'danger', 'flash_message' => 'Bạn không có quyền chỉnh sửa bài viết này']);
        }
        
        // Lấy thông tin tài liệu và URLs từ resources
        $documents = [];
        $urls = [];
        
        if (!empty($post->resources)) {
            $resourceIds = is_array($post->resources) ? $post->resources : json_decode($post->resources, true);
            
            if (is_array($resourceIds) && !empty($resourceIds)) {
                $resources = \App\Modules\Resource\Models\Resource::whereIn('id', $resourceIds)->get();
                
                foreach ($resources as $resource) {
                    if ($resource->link_code == 'file') {
                        // Luôn giữ file là file, không chuyển thành URL
                        $documents[] = [
                            'id' => $resource->id,
                            'name' => $resource->file_name,
                            'url' => $resource->url,
                            'type' => $resource->file_type
                        ];
                    } else {
                        // Chỉ URL thực sự mới được đưa vào danh sách URLs
                        $urls[] = $resource->url;
                    }
                }
            }
        }
        
        $post->documents = $documents;
        $post->urls = $urls;

        // Phục vụ request Ajax để hiển thị trong modal
        if (request()->ajax()) {
            $data = [
                'post' => $post,
                'tags' => TTag::all(),
                'csrf_token' => csrf_token()
            ];
            
            return response()->json($data);
        }

        // Hiển thị trang edit thông thường
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['pagetitle'] = "Chỉnh sửa bài viết";
        $data['tags'] = TTag::all();
        $data['toptags'] = TTag::where('id', '<=', 5)->orderBy('hit')->get();
        $data['post'] = $post;
        
        return view('Tuongtac::frontend.blogs.edit', $data);
    }
    public function destroy($id)
    {
        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        $post = TBlog::findOrFail($id);
        // Kiểm tra nếu người dùng không phải tác giả
        if (auth()->id() == $post->user_id || auth()->user()->role == 'admin') {
            $post->delete();
            return redirect()->route('front.tblogs.index')->with('success', 'Bài viết đã được xóa!');
        } else {
            abort(403, 'Bạn không có quyền xóa bài viết này.');
        }
    }


    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        // Kiểm tra nếu người dùng không phải tác giả
        $post = TBlog::find($id);

        if (auth()->id() == $post->user_id || auth()->user()->role == 'admin') {
            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'photo' => 'nullable',
                'tags' => 'nullable|array', // Tags phải là một mảng
                'document' => 'nullable|array',
                'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:20480',
            ]);

            $data = $request->except(['photo']);  // Loại trừ photo để xử lý riêng
            $tag_ids = $request->tags;

            $helpController = new \App\Http\Controllers\Frontend\HelperController();
            $data['content'] = $helpController->uploadImageInContent($data['content']);

            // Xử lý ảnh từ Dropzone
            if (isset($request->photo) && !empty($request->photo) && $request->photo != 'null' && $request->photo != '[]') {
                try {
                    // Kiểm tra nếu dữ liệu là chuỗi JSON hợp lệ
                    $photoData = json_decode($request->photo, true);

                    if (is_array($photoData)) {
                        // Nếu đã là mảng, sử dụng trực tiếp
                        $data['photo'] = $request->photo;
                    } else {
                        // Nếu không phải mảng, đưa vào mảng và chuyển thành JSON
                        $data['photo'] = json_encode([$request->photo]);
                    }
                } catch (\Exception $e) {
                    // Nếu có lỗi, lưu trực tiếp giá trị
                    $data['photo'] = json_encode([$request->photo]);
                    \Illuminate\Support\Facades\Log::error('Lỗi xử lý photo: ' . $e->getMessage());
                }
            } else {
                // Nếu photo là rỗng, null, hoặc mảng trống, đặt thành mảng trống
                $data['photo'] = json_encode([]);
            }

            // Debug thông tin ảnh
            \Illuminate\Support\Facades\Log::info('Photo trước khi cập nhật: ' . $post->photo);
            \Illuminate\Support\Facades\Log::info('Photo từ request: ' . ($request->photo ?? 'null'));
            \Illuminate\Support\Facades\Log::info('Photo sau khi xử lý: ' . $data['photo']);

            $post->update($data);

            $tagcontroller = new \App\Modules\Tuongtac\Controllers\TTagController();
            $tagcontroller->update_item_tag($post->id, $tag_ids, 'tblog');

            // Xử lý tài liệu đính kèm
            // Lưu danh sách resources hiện tại (trước khi cập nhật)
            $currentResourceIds = is_array($post->resources) ? $post->resources : json_decode($post->resources, true) ?? [];
            $currentResourceIds = is_array($currentResourceIds) ? $currentResourceIds : [];
            \Illuminate\Support\Facades\Log::info('Danh sách resources hiện tại: ', $currentResourceIds);
            
            // Biến theo dõi ID resources cần xóa
            $resourceToRemove = [];

            // Xử lý xóa tài liệu được đánh dấu
            $resourceIdsToKeep = $currentResourceIds;
            if ($request->has('delete_documents') && !empty($request->delete_documents)) {
                try {
                    $resourceIdsToDelete = json_decode($request->delete_documents, true);
                    if (is_array($resourceIdsToDelete) && !empty($resourceIdsToDelete)) {
                        // Lọc ra các resources ID cần giữ lại
                        $resourceIdsToKeep = array_diff($currentResourceIds, $resourceIdsToDelete);
                        
                        // Xóa các tài nguyên từ bảng Resource
                        foreach ($resourceIdsToDelete as $resourceId) {
                            try {
                                $resource = \App\Modules\Resource\Models\Resource::find($resourceId);
                                if ($resource) {
                                    $resource->deleteResource();
                                    \Illuminate\Support\Facades\Log::info('Đã xóa tài nguyên: ' . $resourceId);
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Lỗi khi xóa tài nguyên: ' . $e->getMessage());
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Lỗi xử lý delete_documents: ' . $e->getMessage());
                }
            }

            // Xử lý danh sách URLs bị xóa
            if ($request->has('delete_urls') && !empty($request->delete_urls)) {
                $urlsToDelete = [];
                
                // Xử lý trường hợp chuỗi JSON hoặc mảng
                if (is_string($request->delete_urls)) {
                    try {
                        $urlsToDelete = json_decode($request->delete_urls, true);
                        if (!is_array($urlsToDelete)) {
                            $urlsToDelete = [$request->delete_urls];
                        }
                    } catch (\Exception $e) {
                        $urlsToDelete = [$request->delete_urls];
                    }
                } else if (is_array($request->delete_urls)) {
                    $urlsToDelete = $request->delete_urls;
                }
                
                \Illuminate\Support\Facades\Log::info('Danh sách URLs cần xóa: ', $urlsToDelete);
                
                if (!empty($urlsToDelete) && !empty($currentResourceIds)) {
                    $resources = \App\Modules\Resource\Models\Resource::whereIn('id', $currentResourceIds)
                        ->where('link_code', 'url') // Chỉ xóa các URL, không xóa file
                        ->get();
                    
                    foreach ($resources as $resource) {
                        if (in_array($resource->url, $urlsToDelete)) {
                            try {
                                $resourceToRemove[] = $resource->id;
                                $resource->deleteResource();
                                \Illuminate\Support\Facades\Log::info('Đã xóa URL: ' . $resource->url);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Lỗi khi xóa URL: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    // Cập nhật danh sách resources sẽ giữ lại
                    $resourceIdsToKeep = array_diff($resourceIdsToKeep, $resourceToRemove);
                }
            }
            
            // Thêm tài liệu mới nếu có
            $newDocumentIds = [];
            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $file) {
                    $resource = \App\Modules\Resource\Models\Resource::createResource($request, $file, 'TBlog');
                    // Đảm bảo file được đánh dấu là file chứ không phải URL
                    $resource->link_code = 'file';
                    $resource->save();
                    
                    $newDocumentIds[] = $resource->id;
                    \Illuminate\Support\Facades\Log::info('Đã thêm tài liệu mới: ' . $resource->id);
                }
            }
            
            // Lưu lại URL các file đã upload để không tạo trùng lặp
            $uploadedFileUrls = [];
            if (!empty($newDocumentIds)) {
                $uploadedResources = \App\Modules\Resource\Models\Resource::whereIn('id', $newDocumentIds)->get();
                foreach ($uploadedResources as $res) {
                    $uploadedFileUrls[] = $res->url;
                }
            }

            // Thêm URLs mới nếu có
            $newUrlIds = [];
            if ($request->has('urls') && $request->urls) {
                // Lấy tất cả các URL hiện tại
                $existingUrls = [];
                if (!empty($resourceIdsToKeep)) {
                    $existingResources = \App\Modules\Resource\Models\Resource::whereIn('id', $resourceIdsToKeep)->get();
                    foreach ($existingResources as $resource) {
                        $existingUrls[] = $resource->url;
                    }
                }
                
                \Illuminate\Support\Facades\Log::info('URLs hiện tại: ', $existingUrls);
                
                foreach ($request->urls as $url) {
                    if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                        // Bỏ qua URLs trỏ đến các file đã upload để tránh trùng lặp
                        if (in_array($url, $uploadedFileUrls)) {
                            continue;
                        }
                        
                        // Kiểm tra xem URL đã tồn tại trong resources chưa
                        $exists = false;
                        foreach ($existingUrls as $existingUrl) {
                            if ($existingUrl == $url) {
                                $exists = true;
                                break;
                            }
                        }
                        
                        // Chỉ thêm URL mới nếu nó chưa tồn tại
                        if (!$exists) {
                            $urlTitle = Resource::getYouTubeID($url) ? "YouTube Video" : "Liên kết web";
                            $resource = \App\Modules\Resource\Models\Resource::createUrlResource($urlTitle, $url, 'other', 'tblog');
                            $newUrlIds[] = $resource->id;
                            \Illuminate\Support\Facades\Log::info('Đã thêm URL mới: ' . $url);
                        } else {
                            \Illuminate\Support\Facades\Log::info('Bỏ qua URL trùng lặp: ' . $url);
                        }
                    }
                }
            }
            
            // Gộp tất cả resources lại
            $finalResourceIds = array_merge($resourceIdsToKeep, $newDocumentIds, $newUrlIds);
            \Illuminate\Support\Facades\Log::info('Danh sách resources cuối cùng: ', $finalResourceIds);
            
            // Cập nhật post với danh sách resources mới
            $post->resources = array_values(array_unique($finalResourceIds));
            $post->save();
            
            \Illuminate\Support\Facades\Log::info('Đã cập nhật bài viết với ' . count($post->resources) . ' tài nguyên');

            if ($request->frompage) {
                $page = TPage::where('slug', $request->frompage)->first();
                if ($page)
                    return redirect()->route('group.show', $page->item_id)->with('success', 'Bài viết đã được cập nhật!');
            }
            return redirect()->route('front.tblogs.show', $post->slug)->with('success', 'Bài viết đã được cập nhật!');
        } else {
            abort(403, 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
    }

    /**
     * Phương thức trả về dữ liệu form tạo bài viết mới dưới dạng JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreateForm(Request $request)
    {
        // Kiểm tra xem request có phải là Ajax request không
        if (!$request->ajax() && !$request->header('X-Requested-With')) {
            return response()->json(['error' => 'Only AJAX requests are allowed'], 400);
        }
        
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $toptags = TTag::where('id', '<=', 5)->orderBy('hit')->get();
        $tags = TTag::orderBy('title', 'ASC')->get();
        
        // Đảm bảo response có header Content-Type: application/json
        return response()->json([
            'toptags' => $toptags,
            'tags' => $tags,
            'csrf_token' => csrf_token(),
            'upload_avatar_url' => route('front.upload.avatar'),
            'store_url' => route('front.tblogs.store'),
            'ckeditor_upload_url' => route('admin.upload.ckeditor') . '?_token=' . csrf_token()
        ], 200, ['Content-Type' => 'application/json']);
    }
}

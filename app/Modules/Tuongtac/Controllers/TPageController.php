<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Modules\Tuongtac\Models\TPage;
use App\Modules\Tuongtac\Models\TSurvey;
use App\Modules\Group\Models\Group;

class TPageController extends Controller
{
    public function updateImage(Request $request)
    {
        $request->validate([
            'page_id' => 'integer|required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:20480',
        ]);
        $page = TPage::find($request->page_id); // Thay ID thực tế
        if (!$page) {
            return redirect()->back()->with('error', 'không tìm thấy dữ liệu!');
        }
        $is_admin = $this->check_quyendieuchinh($page);
        if (!$is_admin)
            return redirect()->back()->with('error', "Bạn không có quyền!");
        $type = $request->input('type');
        $file = $request->file('image');
        if ($file) {

            // Lưu đường dẫn vào database

            $user = auth()->user();
            // GroupMemeber

            $filesController = new \App\Http\Controllers\FilesController();
            $folder = 'avatar';
            $url = $filesController->store($file, $folder);
            if ($type == 'banner') {
                $page->banner = $url;
            } else {
                $page->avatar = $url;
            }
            $page->save();
        }
        return redirect()->back()->with('success', 'Cập nhật hình ảnh thành công!');
    }
    public function viewsurvey(Request $request, $slug)
    {
        $page = TPage::where('slug', $slug)->first();
        if (!$page) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu!');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['categories'] = \App\Models\Category::where('status', 'active')->where('parent_id', null)->get();
        $user  = auth()->user();
        ////
        $data['pagetitle'] = "Trang " . $page->title;

        $data['page_up_title'] = "Trang " . $page->title;
        $data['page_subtitle'] = "Trang " . $page->title;
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Trang " . $page->title;
        $data['page'] = $page;
        $is_admin = 0;
        if ($page->item_code = "group") {
            $group = Group::find($page->item_id);
            if (! $group) {
                return redirect()->back()->with('error', 'Không tìm thấy dữ liệu!');
            } else {

                if ($user &&  $group->getRole($user->id) != 'member' && $group->getRole($user->id) != '') {
                    $data['role'] = $group->getRole($user->id);
                    $is_admin = 1;
                }
            }
        }

        $data['is_admin'] = $is_admin;


        // load bai viet

        $data['surveys'] = TSurvey::where('item_id', $page->id)->where('item_code', 'page')->orderBy('id', 'desc')->paginate(20);


        // $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'page';

        return view('Tuongtac::frontend.pages.survey', $data);
    }
    public function check_quyendieuchinh($page)
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
                if ($user &&  $group->getRole($user->id) != 'member' && $group->getRole($user->id) != '') {
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
    public function view(Request $request, $slug)
    {

        $page = TPage::where('slug', $slug)->first();
        if (!$page) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu!1');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['categories'] = \App\Models\Category::where('status', 'active')->where('parent_id', null)->get();
        $user  = auth()->user();
        ////
        $data['pagetitle'] = "Trang " . $page->title;

        $data['page_up_title'] = "Trang " . $page->title;
        $data['page_subtitle'] = "Trang " . $page->title;
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Trang " . $page->title;
        $data['page'] = $page;
        $is_admin = 0;


        $data['is_admin'] = $this->check_quyendieuchinh($page);


        // load bai viet
        $data['surveys'] = \DB::table('t_surveys')
            ->join('t_page_items', 't_surveys.id', '=', 't_page_items.item_id')
            ->where('t_page_items.item_code', 'survey') // Lọc theo item_code (nếu cần)
            ->where('t_page_items.page_id', $page->id)   // Lọc theo tag_id
            ->whereRaw('t_surveys.expired_date > NOW()')
            ->select('t_surveys.*', 't_page_items.order_id')
            ->orderBy('t_page_items.order_id', 'desc')
            ->paginate(40);

        $data['posts'] = \DB::table('t_blogs')
            ->join('t_page_items', 't_blogs.id', '=', 't_page_items.item_id')
            ->where('t_page_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
            ->where('t_page_items.page_id', $page->id)   // Lọc theo tag_id
            ->where('t_blogs.status', 1)
            ->select('t_blogs.*', 't_page_items.order_id')
            ->orderBy('t_page_items.order_id', 'desc')
            ->paginate(40);



        // $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog) {
            $blog->user_url  = "";
            $userpage = TPage::where('item_id', $blog->user_id)->where('item_code', 'user')->first();
            if ($userpage) {
                $blog->user_url  = route('front.tpage.view', $userpage->slug);
                // dd($data['user_url']);
            }

            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml = \App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog');
            $blog->actionbar = \App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog');
        }
        // dd($blog->author);
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar();

        return view('Tuongtac::frontend.pages.index', $data);
    }

    public function viewgroup(Request $request, $slug)
    {
        $page = TPage::where('slug', $slug)->first();
        if (!$page) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu!');
        }
        
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['categories'] = \App\Models\Category::where('status', 'active')->where('parent_id', null)->get();
        $user = auth()->user();
        
        // Thông tin trang
        $data['pagetitle'] = "Trang " . $page->title;
        $data['page_up_title'] = "Trang " . $page->title;
        $data['page_subtitle'] = "Trang " . $page->title;
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page'] = $page;
        
        // Kiểm tra quyền điều chỉnh
        $data['is_admin'] = $this->check_quyendieuchinh($page);
        
        // Tìm kiếm nhóm nếu có
        $search = $request->input('search');
        
        // Lấy tất cả nhóm (sẽ hiển thị nhóm công khai và riêng tư) với phân trang
        $allGroupsQuery = \App\Modules\Group\Models\Group::where('status', 'active');
        
        // Thêm điều kiện tìm kiếm nếu có
        if ($search) {
            $allGroupsQuery->where('title', 'like', '%' . $search . '%');
        }
        
        // Lấy danh sách tất cả nhóm
        $data['publicGroups'] = $allGroupsQuery->orderBy('id', 'desc')
                               ->paginate(12, ['*'], 'public_page');
        
        // Lấy thông tin nhóm của tôi (nhóm mà người dùng là thành viên hoặc tác giả)
        if ($user) {
            // Lấy danh sách nhóm mà người dùng là thành viên
            $memberGroups = \App\Modules\Group\Models\GroupMember::where('user_id', $user->id)
                ->pluck('group_id')
                ->toArray();
                
            $userGroupsQuery = \App\Modules\Group\Models\Group::where('status', 'active')
                ->where(function($query) use ($user, $memberGroups) {
                    $query->where('author_id', $user->id)
                          ->orWhereIn('id', $memberGroups);
                });
            
            // Thêm điều kiện tìm kiếm nếu có
            if ($search) {
                $userGroupsQuery->where('title', 'like', '%' . $search . '%');
            }
            
            $data['userGroups'] = $userGroupsQuery->orderBy('id', 'desc')
                                ->paginate(12, ['*'], 'my_page');
        } else {
            $data['userGroups'] = collect([]);
        }
        
        return view('Tuongtac::frontend.pages.group_view', $data);
    }
}

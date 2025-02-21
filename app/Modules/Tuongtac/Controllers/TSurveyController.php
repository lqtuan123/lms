<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TSurvey;
use  App\Modules\Tuongtac\Models\TPage;
use  App\Modules\Tuongtac\Models\TQuestion;
use  App\Modules\Tuongtac\Models\TOption;
use  App\Modules\Tuongtac\Models\TPageItem;
use  App\Modules\Group\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TSurveyController extends Controller
{

    public function create(Request $request, $item_id, $item_code)
    {
        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);

        ////
        $data['pagetitle'] = "Thêm thông tin thăm dò";

        $data['page_up_title'] = 'Thêm thông tin thăm dò';
        $data['page_subtitle'] = "Thêm thông tin thăm dò";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Thêm thông tin thăm dò";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Thêm thông tin thăm dò ";

        $data['item_id'] = $item_id;
        $data['item_code'] = $item_code;
        return view('Tuongtac::frontend.surveys.create', $data);
    }
    public function addquestion($id)
    {
        $user  = auth()->user();
        if (!$user) {
            return redirect()->route('front.login');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $user  = auth()->user();
        ////
        $data['pagetitle'] = "Thêm câu hỏi cho survey";

        $data['page_up_title'] = 'Thêm câu hỏi cho survey';
        $data['page_subtitle'] = "Thêm câu hỏi cho survey";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Thêm câu hỏi cho survey";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Thêm câu hỏi cho survey ";
        $data['survey_id'] = $id;
        $survey = TSurvey::find($id);
        if (!$survey) {
            return redirect()->back()->with('error', 'không tìm thấy nhóm thăm dò');
        }

        return view('Tuongtac::frontend.surveys.addquestion', $data);
    }
    public function savequestion(Request $request)
    {

        if (!auth()->id())
            return redirect()->back()->with('error', 'Chưa đăng nhập!');
        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
            'answers' => 'required|array|min:1',
            'answers.*' => 'nullable|string|max:255',
            'survey_id' => 'required|integer',
        ]);
        $survey = TSurvey::find($request->survey_id);
        if (!$survey)
            return redirect()->back()->with('error', 'Không tìm thấy');
        if (auth()->id() == $survey->user_id || auth()->user()->role == 'admin') {
            $question = TQuestion::create([
                'question' => $validated['question_text'],
                'survey_id' => $request->survey_id,
                'user_id' => auth()->id(),
            ]);

            // Lưu các câu trả lời
            // dd($validated['answers']);
            foreach ($validated['answers'] as $answer) {
                if ($answer) {
                    $question->options()->create([
                        'option_text' => $answer,
                        'user_id' => auth()->id(),
                        'votes' => 0, // Khởi tạo số lượt bình chọn là 0
                    ]);
                }
            }
            return redirect()->route('front.surveys.show', $survey->slug)->with('success', 'Câu hỏi và câu trả lời đã được thêm thành công!');
        } else {
            return redirect()->back()->with('error', 'bạn không có quyền!');
        }
        // Lưu câu hỏi



    }
    public function editquestion($id)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Bạn chưa đăng nhập!');
        }
        $question = TQuestion::with('options')->findOrFail($id); // Lấy câu hỏi và câu trả lời
        if ($question && ($question->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $survey = TSurvey::find($question->survey_id);
            if (!$survey) {
                return redirect()->back()->with('error', 'không tìm thấy!');
            }

            $data['detail'] = \App\Models\SettingDetail::find(1);
            $user  = auth()->user();
            ////
            $data['pagetitle'] = "Thêm câu hỏi cho survey";
            $data['page_up_title'] = 'Thêm câu hỏi cho survey';
            $data['page_subtitle'] = "Thêm câu hỏi cho survey";
            $data['page_title'] = " ";
            $data['hotbutton_title'] = "Thêm câu hỏi cho survey";
            $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
            $data['hotbutton_link'] = "";
            $data['page_up_title'] = "Thêm câu hỏi cho survey ";
            $data['survey_id'] = $id;
            $data['question'] = $question;
            return view('Tuongtac::frontend.surveys.editquestion', $data);
        } else {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu!');
        }
    }
    public function destroyquestion($id)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Bạn chưa đăng nhập!');
        }
        $question = TQuestion::find($id);
        if ($question && ($question->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $question->delete();
        }
        return redirect()->back()->with('success', 'Đã xóa thành công!');
    }
    public function xem(Request $request, $slug)
    {
        $user = auth()->user();
        $data['survey'] = TSurvey::where('slug', $slug)->first();
        $data['detail'] = \App\Models\SettingDetail::find(1);
        $data['user'] = $user;
        //  dd($user, $data['survey']);
        ////
        $survey = TSurvey::where('slug', $slug)->first();
        $data['pagetitle'] = "Thêm thông tin thăm dò";

        $data['page_up_title'] = 'Thêm thông tin thăm dò';
        $data['page_subtitle'] = "Thêm thông tin thăm dò";
        $data['page_title'] = " ";
        $data['hotbutton_title'] = "Thêm thông tin thăm dò";
        $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
        $data['hotbutton_link'] = "";
        $data['page_up_title'] = "Thêm thông tin thăm dò ";

        $data['questions'] = TQuestion::with('options')->where('survey_id', $survey->id)->get();


        return view('Tuongtac::frontend.surveys.show', $data);
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
    public function store(Request $request)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Chưa đăng nhập');
        }

        // Validate đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'item_id' => 'required|integer',
            'item_code' => 'required|string|max:255',
            'expired_date' => 'nullable|date',
        ]);

        // Tìm trang liên quan
        $item_id = $request->item_id;
        $item_code = $request->item_code;
        $page = TPage::find($item_id);
        if (!$page) {
            return redirect()->back()->with('error', 'Không tìm thấy trang');
        }

        // Kiểm tra quyền đăng bài
        if ($this->check_quyendangbai($page)) {
            // Chuẩn bị dữ liệu để lưu
            $data = $request->all();
            $slug = Str::slug($request->input('name'));
            $slug_count = TSurvey::where('slug', $slug)->count();

            // Xử lý trường hợp trùng slug
            if ($slug_count > 0) {
                $slug .= '-' . time();
            }

            $data['slug'] = $slug;
            $data['user_id'] = auth()->id();

            // Đảm bảo `user_ids` luôn có giá trị mặc định
            $data['user_ids'] = $data['user_ids'] ?? []; // Nếu không có, đặt mặc định là mảng rỗng

            // Tạo Survey
            $survey = TSurvey::create($data);

            // Tạo Page Item
            $datam = [
                'item_id' => $survey->id,
                'item_code' => 'survey',
                'page_id' => $data['item_id'],
            ];
            $item = TPageItem::create($datam);
            $item->order_id = $item->id;
            $item->save();

            // Điều hướng sau khi thành công
            if ($data['item_code'] === "page") {
                $page = TPage::find($data['item_id']);
                if ($page) {
                    return redirect()->route('front.pagesurvey.index', $page->slug)
                        ->with('success', 'Survey đã được thêm thành công!');
                }
            }
        } else {
            return redirect()->back()->with('error', 'Không có quyền đăng');
        }
    }

    public function updatequestion(Request $request, $id)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Bạn chưa đăng nhập!');
        }

        $validated = $request->validate([
            'question_text' => 'required|string|max:255',
            'answers' => 'required|array|min:1',
            'answers.*.id' => 'nullable|exists:t_options,id', // ID của các câu trả lời
            'answers.*.text' => 'required|string|max:255', // Nội dung câu trả lời
        ]);

        // Lấy câu hỏi từ database
        $question = TQuestion::findOrFail($id);
        if ($question  && ($question->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $survey = TSurvey::find($question->survey_id);
            if (!$survey) {
                return redirect()->back()->with('error', 'không tìm thấy!');
            }
            // Cập nhật nội dung câu hỏi
            $question->update(['question' => $validated['question_text']]);

            // Xử lý cập nhật câu trả lời
            foreach ($validated['answers'] as $answer) {
                if (isset($answer['id'])) {
                    // Nếu có `id`, cập nhật câu trả lời cũ
                    TOption::where('id', $answer['id'])->update(['option_text' => $answer['text']]);
                } else {
                    // Nếu không có `id`, tạo mới câu trả lời
                    $question->options()->create(['option_text' => $answer['text'], 'votes' => 0, 'user_id' => auth()->id()]);
                }
            }
        } else {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu hoặc bạn không có quyền!');
        }

        return redirect()->route('front.surveys.show',  $survey->slug)->with('success', 'Câu hỏi và câu trả lời đã được cập nhật!');
    }
    public function editsurvey(Request $request, $id)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Bạn chưa đăng nhập!');
        }
        $survey = TSurvey::find($id);
        if ($survey && ($survey->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $data['survey'] = $survey;
            $data['detail'] = \App\Models\SettingDetail::find(1);
            $data['user'] = auth()->user();
            //  dd($user, $data['survey']);
            ////

            $data['pagetitle'] = "Thêm thông tin thăm dò";

            $data['page_up_title'] = 'Thêm thông tin thăm dò';
            $data['page_subtitle'] = "Thêm thông tin thăm dò";
            $data['page_title'] = " ";
            $data['hotbutton_title'] = "Thêm thông tin thăm dò";
            $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
            $data['hotbutton_link'] = "";
            $data['page_up_title'] = "Thêm thông tin thăm dò ";
            if ($request->frompage)
                $data['frompage'] = $request->frompage;
            return view('Tuongtac::frontend.surveys.edit', $data);
        } else {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu hoặc bạn không có quyền!');
        }
    }
    public function updatesurvey(Request $request, $id)
    {
        if (!auth()->id()) {
            return redirect()->back()->with('error', 'Bạn chưa đăng nhập!');
        }
        $validated = $request->validate([

            'name' => 'required|string|max:255',
            'item_id' => 'required|integer',
            'item_code' => 'required|string|max:255',
            'expired_date' => 'nullable|date',
        ]);
        $survey = TSurvey::find($id);

        if ($survey && ($survey->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $survey = TSurvey::findOrFail($id);
            // Cập nhật nội dung câu hỏi
            $survey->update(['name' => $validated['name'], 'expired_date' => $validated['expired_date']]);
            if ($request->frompage) {
                return redirect()->route('front.pagesurvey.index', $request->frompage)->with('success', 'cập nhật thành công!');
            }
            return redirect()->route('front.tblogs.index')->with('success', 'cập nhật thành công!');
        } else {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu hoặc bạn không có quyền!');
        }
    }
    public function destroysurvey(Request $request, $id)
    {
        $survey = TSurvey::find($id);

        if ($survey && ($survey->user_id == auth()->id() || auth()->user()->role == 'admin')) {
            $survey->delete();
            return redirect()->back()->with('success', 'Bạn đã xóa thành công!');
        } else {
            return redirect()->back()->with('error', 'Bạn không có quyền hoặc không tìm thấy dữ liệu!');
        }
    }
}

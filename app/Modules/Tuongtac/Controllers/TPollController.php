<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TOption;
use  App\Modules\Tuongtac\Models\TQuestion;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TSurvey;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TPollController extends Controller
{
  

    // Xử lý câu trả lời
    public function voteAll(Request $request)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:t_surveys,id',
            'answers' => 'required|array', // Danh sách câu trả lời cho mỗi câu hỏi
            'answers.*' => 'nullable|string', // Có thể là ID của tùy chọn cũ hoặc giá trị của tùy chọn mới
        ]);
        $survey = TSurvey::find($request->survey_id);
        if(!$survey)
            return redirect()->back()->with('error','Không tìm thấy');

        $userId = auth()->id();
         // Lấy survey từ database
        $survey = TSurvey::findOrFail($request->survey_id);

        // Lấy danh sách user_ids hiện tại (nếu null thì gán là mảng rỗng)
        $userIds = $survey->user_ids ?? [];

        // Kiểm tra nếu user đã tồn tại trong danh sách
        if (in_array($userId, $userIds)) {
            return redirect()->back()->with('error', 'Bạn đã trả lời survey này!');
        }

        // Thêm userId vào mảng
        $userIds[] = $userId;

        // Lưu lại mảng user_ids vào database
        $survey->update(['user_ids' => $userIds]);
      
        // foreach ($validated['new_options'] as $questionId => $newOptions) {
        //     foreach ($newOptions as $optionText) {
        //         Option::create([
        //             'question_id' => $questionId,
        //             'option_text' => $optionText,
        //             'votes' => 0,
        //         ]);
        //     }
        // }

        // // Tăng lượt chọn cho các lựa chọn được chọn
        // foreach ($validated['answers'] as $optionId) {
        //     TOption::where('id', $optionId)->increment('votes');
        // }
        
          // Tạo mảng để lưu các tùy chọn được chọn (bao gồm cả mới và cũ)
        $selectedOptionIds = [];

        DB::transaction(function () use ($validated) {
            // Danh sách ID của các tùy chọn được chọn
            $selectedOptionIds = [];
    
            foreach ($validated['answers'] as $questionId => $optionValue) {
                if (is_numeric($optionValue)) {
                    // Nếu là ID của tùy chọn cũ
                    $selectedOptionIds[] = $optionValue;
                } else {
                    // Nếu là tùy chọn mới
                    $newOption = TOption::create([
                        'question_id' => $questionId,
                        'option_text' => $optionValue,
                        'votes' => 0, // Lượt chọn ban đầu
                        'user_id'=>auth()->id(),
                    ]);
    
                    // Ghi nhận ID của tùy chọn mới
                    $selectedOptionIds[] = $newOption->id;
                }
            }
    
            // Tăng số lượt chọn cho tất cả các tùy chọn được chọn
            TOption::whereIn('id', $selectedOptionIds)->increment('votes');
        });

        return redirect()->route('front.surveys.show',$survey->slug)->with('success','Đã nhận bình chọn!');
        // return redirect()->route('poll.index')->with('success', 'Cảm ơn bạn đã trả lời tất cả các câu hỏi!');
    }
}
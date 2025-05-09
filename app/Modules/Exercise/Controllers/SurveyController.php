<?php

namespace App\Modules\Exercise\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Modules\Exercise\Models\Survey;
use App\Modules\Exercise\Models\SurveyQuestion;
use App\Modules\Teaching_2\Models\HocPhan;
use App\Modules\Teaching_1\Models\Teacher;

class SurveyController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->pagesize = env('NUMBER_PER_PAGE', 20);
        $this->middleware('auth');
    }

    // List all surveys
    public function index()
    {
        $active_menu = 'survey_list';
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Danh sách khảo sát</li>';

        $surveys = Survey::with(['hocphan', 'giangvien'])
            ->orderBy('id', 'DESC')
            ->paginate($this->pagesize);

        // Add question count to each survey
        $surveys->getCollection()->transform(function ($survey) {
            $survey->question_count = $survey->questions()->count();
            return $survey;
        });

        $hocPhanList = HocPhan::pluck('title', 'id')->toArray();
        $teacherList = Teacher::with('user')->get();

        return view('Exercise::survey.index', compact('surveys', 'breadcrumb', 'active_menu', 'hocPhanList', 'teacherList'));
    }

    // Show the form for creating a new survey
    public function create()
    {
        $active_menu = 'survey_add';
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thêm khảo sát</li>';

        $hocPhanList = HocPhan::all();
        $teacherList = Teacher::with('user')->get();

        return view('Exercise::survey.create', compact('hocPhanList', 'teacherList', 'breadcrumb', 'active_menu'));
    }

    // Store a new survey
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hocphan_id' => 'required|exists:hoc_phans,id',
            'giangvien_id' => 'required|exists:teacher,id',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.type' => 'required|in:text,multiple_choice',
        ]);

        try {
            // Create survey
            $survey = Survey::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'hocphan_id' => $validatedData['hocphan_id'],
                'giangvien_id' => $validatedData['giangvien_id'],
            ]);

            // Store questions
            foreach ($validatedData['questions'] as $questionData) {
                $options = $questionData['type'] === 'multiple_choice' ? ['1', '2', '3', '4', '5'] : [null];
                SurveyQuestion::create([
                    'survey_id' => $survey->id,
                    'question' => $questionData['question'],
                    'type' => $questionData['type'],
                    'options' => $options,
                ]);
            }

            return redirect()->route('admin.survey.index')->with('success', 'Khảo sát được tạo thành công.');
        } catch (\Exception $e) {
            Log::error('Error creating survey:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi tạo khảo sát.');
        }
    }

    // Show a specific survey
    public function show(Survey $survey)
    {
        $active_menu = 'survey_show';
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chi tiết khảo sát</li>';

        $survey->load(['hocphan', 'giangvien', 'questions']);

        return view('Exercise::survey.show', compact('survey', 'active_menu', 'breadcrumb'));
    }

    // Show the form for editing a survey
    public function edit($id)
    {
        $active_menu = 'survey_edit';
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa khảo sát</li>';

        $survey = Survey::with('questions')->findOrFail($id);
        $hocPhanList = HocPhan::all();
        $teacherList = Teacher::with('user')->get();

        return view('Exercise::survey.edit', compact('survey', 'hocPhanList', 'teacherList', 'breadcrumb', 'active_menu'));
    }

    // Update a survey
    public function update(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'hocphan_id' => 'required|exists:hoc_phans,id',
            'giangvien_id' => 'required|exists:teacher,id',
            'questions' => 'required|array|min:1',
            'questions.*.id' => 'nullable|exists:survey_questions,id',
            'questions.*.question' => 'required|string|max:255',
            'questions.*.type' => 'required|in:text,multiple_choice',
        ]);

        try {
            // Update survey
            $survey->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'hocphan_id' => $validatedData['hocphan_id'],
                'giangvien_id' => $validatedData['giangvien_id'],
            ]);

            // Get existing question IDs
            $existingQuestionIds = $survey->questions()->pluck('id')->toArray();
            $submittedQuestionIds = array_filter(array_column($validatedData['questions'], 'id'));

            // Delete questions that are no longer in the form
            SurveyQuestion::where('survey_id', $survey->id)
                ->whereNotIn('id', $submittedQuestionIds)
                ->delete();

            // Update or create questions
            foreach ($validatedData['questions'] as $questionData) {
                $options = $questionData['type'] === 'multiple_choice' ? ['1', '2', '3', '4', '5'] : [null];
                $question = isset($questionData['id'])
                    ? SurveyQuestion::find($questionData['id'])
                    : new SurveyQuestion();

                $question->survey_id = $survey->id;
                $question->question = $questionData['question'];
                $question->type = $questionData['type'];
                $question->options = $options;
                $question->save();
            }

            return redirect()->route('admin.survey.index')->with('success', 'Khảo sát được cập nhật thành công.');
        } catch (\Exception $e) {
            Log::error('Error updating survey:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi cập nhật khảo sát.');
        }
    }

    // Delete a survey
    public function destroy($id)
    {
        try {
            $survey = Survey::findOrFail($id);
            $survey->questions()->delete(); // Delete associated questions
            // Note: Responses are not deleted to preserve data; adjust if needed
            $survey->delete();

            return redirect()->route('admin.survey.index')->with('success', 'Khảo sát đã được xóa thành công.');
        } catch (\Exception $e) {
            Log::error('Error deleting survey:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi xóa khảo sát.');
        }
    }
}
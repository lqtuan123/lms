<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyAnswer;
use App\Modules\Exercise\Models\Survey as ModelsSurvey;
use App\Modules\Exercise\Models\SurveyAnswer as ModelsSurveyAnswer;
use App\Modules\Exercise\Models\SurveyResponse as ModelsSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    // Lấy thông tin khảo sát và danh sách câu hỏi
    public function getSurvey(Request $request, $hocphanId)
    {
        $request->merge(['hocphan_id' => $hocphanId]);

        $request->validate([
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'student_id' => 'required|integer|exists:students,id',
        ]);

        try {
            $survey = ModelsSurvey::where('hocphan_id', $hocphanId)
                ->with('questions')
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khảo sát cho học phần này',
                ], 404);
            }

            // Kiểm tra xem sinh viên đã gửi khảo sát chưa
            $hasSubmitted = ModelsSurveyResponse::where('survey_id', $survey->id)
                ->where('student_id', $request->student_id)
                ->exists();

            return response()->json([
                'success' => true,
                'message' => 'Thông tin khảo sát',
                'data' => [
                    'survey_id' => $survey->id,
                    'hocphan_id' => $survey->hocphan_id,
                    'giangvien_id' => $survey->giangvien_id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'questions' => $survey->questions->map(function ($question) {
                        return [
                            'question_id' => $question->id,
                            'question' => $question->question,
                            'type' => $question->type,
                            'options' => $question->options,
                        ];
                    }),
                    'has_submitted' => $hasSubmitted,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Gửi kết quả khảo sát
    public function submitSurvey(Request $request, $hocphanId)
    {
        $request->merge(['hocphan_id' => $hocphanId]);

        $request->validate([
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'student_id' => 'required|integer|exists:students,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:survey_questions,id',
            'answers.*.answer' => 'required|string',
        ]);

        try {
            $survey = ModelsSurvey::where('hocphan_id', $hocphanId)->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khảo sát cho học phần này',
                ], 404);
            }

            // Kiểm tra xem sinh viên đã gửi khảo sát chưa
            $existingResponse = ModelsSurveyResponse::where('survey_id', $survey->id)
                ->where('student_id', $request->student_id)
                ->first();

            if ($existingResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi khảo sát này rồi',
                ], 400);
            }

            // Tạo phản hồi khảo sát
            $response = ModelsSurveyResponse::create([
                'survey_id' => $survey->id,
                'student_id' => $request->student_id,
                'hocphan_id' => $survey->hocphan_id,
                'giangvien_id' => $survey->giangvien_id,
                'submitted_at' => now(),
            ]);

            // Lưu câu trả lời
            foreach ($request->answers as $answer) {
                ModelsSurveyAnswer::create([
                    'response_id' => $response->id,
                    'question_id' => $answer['question_id'],
                    'answer' => $answer['answer'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gửi khảo sát thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Xem kết quả khảo sát (dành cho giảng viên)
    public function getSurveyResults(Request $request, $hocphanId)
    {
        $request->merge(['hocphan_id' => $hocphanId]);

        $request->validate([
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'giangvien_id' => 'required|integer|exists:teacher,id', // Sửa 'teacher' thành 'giang_viens' nếu bảng của bạn là giang_viens
        ]);

        try {
            $survey = ModelsSurvey::where('hocphan_id', $hocphanId) // Sửa ModelsSurvey thành Survey
                ->where('giangvien_id', $request->giangvien_id)
                ->with(['responses.student', 'responses.answers.question'])
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khảo sát cho học phần này',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kết quả khảo sát',
                'data' => [
                    'survey_id' => $survey->id,
                    'hocphan_id' => $survey->hocphan_id,
                    'title' => $survey->title,
                    'responses' => $survey->responses->map(function ($response) {
                        return [
                            'response_id' => $response->id,
                            'student_id' => $response->student_id,
                            'student_name' => $response->student->user->full_name ?? 'Không xác định', // Sửa 'name' thành 'full_name' để khớp với bảng users
                            'submitted_at' => $response->submitted_at ? $response->submitted_at->toIso8601String() : null, // Kiểm tra null trước khi gọi toIso8601String
                            'answers' => $response->answers->map(function ($answer) {
                                return [
                                    'question_id' => $answer->question_id,
                                    'question' => $answer->question->question,
                                    'answer' => $answer->answer,
                                ];
                            }),
                        ];
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentSurveys(Request $request, $studentId)
{
    $request->merge(['student_id' => $studentId]);

    $request->validate([
        'student_id' => 'required|integer|exists:students,id',
    ]);

    try {
        // Lấy danh sách học phần mà sinh viên đang học
        $hocPhanIds = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->where('enrollments.student_id', $studentId)
            ->pluck('phancong.hocphan_id')
            ->toArray();

        if (empty($hocPhanIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Sinh viên không tham gia học phần nào',
            ], 404);
        }

        // Lấy danh sách khảo sát của các học phần
        $surveys = ModelsSurvey::whereIn('hocphan_id', $hocPhanIds)
            ->with('hocphan')
            ->get();

        if ($surveys->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có khảo sát nào cho các học phần của bạn',
            ], 404);
        }

        // Kiểm tra trạng thái đã gửi của từng khảo sát
        $surveys = $surveys->map(function ($survey) use ($studentId) {
            $hasSubmitted = ModelsSurveyResponse::where('survey_id', $survey->id)
                ->where('student_id', $studentId)
                ->exists();

            return [
                'survey_id' => $survey->id,
                'hocphan_id' => $survey->hocphan_id,
                'hocphan_title' => $survey->hocphan->title ?? 'Không xác định',
                'title' => $survey->title,
                'description' => $survey->description,
                'has_submitted' => $hasSubmitted,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách khảo sát của sinh viên',
            'data' => $surveys,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}
}
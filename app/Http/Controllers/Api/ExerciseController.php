<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Exercise\Models\Assignment;
use App\Modules\Exercise\Models\TracNghiemCauhoi;
use App\Modules\Exercise\Models\TracNghiemDapan;
use App\Modules\Exercise\Models\BodeTracNghiem;
use App\Modules\Exercise\Models\BoDeTuLuan;
use App\Modules\Exercise\Models\TracNghiemSubmission;
use App\Modules\Exercise\Models\TuLuanCauHoi;
use App\Modules\Exercise\Models\TuLuanSubmission;
use App\Modules\Teaching_1\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    /**
     * Tạo câu hỏi trắc nghiệm
     */
    public function storeQuestion(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'resources' => 'nullable|array',
            'resources.*' => 'integer|exists:resources,id',
            'loai_id' => 'required|integer|exists:trac_nghiem_loais,id',
            'user_id' => 'required|integer|exists:users,id', // Thêm user_id vào validation
        ]);

        try {
            $question = TracNghiemCauhoi::create([
                'content' => $request->content,
                'hocphan_id' => $request->hocphan_id,
                'resources' => $request->resources ? json_encode($request->resources) : null,
                'loai_id' => $request->loai_id,
                'user_id' => $request->user_id, // Lấy từ body request
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo câu hỏi thành công',
                'data' => $question,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo câu hỏi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo đáp án cho câu hỏi trắc nghiệm
     */
    public function storeAnswer(Request $request)
    {
        $request->validate([
            'tracnghiem_id' => 'required|integer|exists:trac_nghiem_cauhois,id',
            'content' => 'required|string|max:500',
            'resounce_list' => 'nullable|array',
            'resounce_list.*' => 'integer|exists:resources,id',
            'is_correct' => 'required|boolean',
        ]);

        try {
            $answer = TracNghiemDapan::create([
                'tracnghiem_id' => $request->tracnghiem_id,
                'content' => $request->content,
                'resounce_list' => $request->resounce_list ? json_encode($request->resounce_list) : null,
                'is_correct' => $request->is_correct,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo đáp án thành công',
                'data' => $answer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đáp án: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo đề thi trắc nghiệm
     */
    public function storeQuiz(Request $request)
{
    try {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'time' => 'required|integer|min:1',
            'tags' => 'nullable|string|max:255',
            'total_points' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.id_question' => 'required|integer|exists:trac_nghiem_cauhois,id',
            'questions.*.points' => 'required|integer|min:1',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $calculatedTotal = collect($request->questions)->sum('points');
        if ($calculatedTotal != $request->total_points) {
            return response()->json([
                'success' => false,
                'message' => 'Tổng điểm của các câu hỏi không khớp với total_points',
            ], 400);
        }

        $quiz = BodeTracNghiem::create([
            'title' => $request->title,
            'hocphan_id' => $request->hocphan_id,
            'slug' => Str::slug($request->title . '-' . time()),
            'start_time' => Carbon::parse($request->start_time),
            'end_time' => Carbon::parse($request->end_time),
            'time' => $request->time,
            'tags' => $request->tags,
            'user_id' => $request->user_id,
            'total_points' => $request->total_points,
            'questions' => json_encode($request->questions),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo đề thi thành công',
            'data' => $quiz,
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi tạo đề thi: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Lấy danh sách câu hỏi theo học phần
     */
    public function getQuestionsByHocphan(Request $request)
    {
        $request->validate([
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'user_id' => 'required|integer|exists:users,id', // Thêm user_id vào validation nếu cần lọc
        ]);

        try {
            $questions = TracNghiemCauhoi::where('hocphan_id', $request->hocphan_id)
                ->where('user_id', $request->user_id) // Lọc theo user_id từ request
                ->with(['answers' => function ($query) {
                    $query->select('id', 'tracnghiem_id', 'content', 'is_correct');
                }])
                ->get(['id', 'content', 'hocphan_id', 'loai_id']);

            return response()->json([
                'success' => true,
                'message' => 'Danh sách câu hỏi trắc nghiệm',
                'data' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách câu hỏi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Lấy danh sách loại câu hỏi
 */
public function getQuestionTypes(Request $request)
{
    try {
        $types = \App\Modules\Exercise\Models\TracNghiemLoai::all(['id', 'title']);
        
        return response()->json([
            'success' => true,
            'message' => 'Danh sách loại câu hỏi',
            'data' => $types,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách loại câu hỏi: ' . $e->getMessage(),
        ], 500);
    }
}

// Tao cau hoi tu luan
public function storeEssayQuestion(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'resources' => 'nullable|array',
            'resources.*' => 'integer|exists:resources,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $question = TuLuanCauHoi::create([
                'content' => $request->content,
                'hocphan_id' => $request->hocphan_id,
                'resources' => $request->resources ? json_encode($request->resources) : null,
                'user_id' => $request->user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo câu hỏi tự luận thành công',
                'data' => $question,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo câu hỏi tự luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo bộ đề tự luận
     */
    public function storeEssayQuiz(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'time' => 'required|integer|min:1',
            'tags' => 'nullable|string|max:255',
            'total_points' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.id_question' => 'required|integer|exists:tu_luan_cauhois,id',
            'questions.*.points' => 'required|integer|min:1',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $calculatedTotal = collect($request->questions)->sum('points');
            if ($calculatedTotal != $request->total_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tổng điểm của các câu hỏi không khớp với total_points',
                ], 400);
            }

            $quiz = BoDeTuLuan::create([
                'title' => $request->title,
                'hocphan_id' => $request->hocphan_id,
                'slug' => Str::slug($request->title . '-' . time()),
                'start_time' => Carbon::parse($request->start_time),
                'end_time' => Carbon::parse($request->end_time),
                'time' => $request->time,
                'tags' => $request->tags,
                'user_id' => $request->user_id,
                'total_points' => $request->total_points,
                'questions' => json_encode($request->questions),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo bộ đề tự luận thành công',
                'data' => $quiz,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo bộ đề tự luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateQuiz(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'hocphan_id' => 'sometimes|integer|exists:hoc_phans,id',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
                'time' => 'sometimes|integer|min:1',
                'tags' => 'nullable|string|max:255',
                'total_points' => 'sometimes|integer|min:1',
                'questions' => 'sometimes|array|min:1',
                'questions.*.id_question' => 'required_with:questions|integer|exists:trac_nghiem_cauhois,id',
                'questions.*.points' => 'required_with:questions|integer|min:1',
                'user_id' => 'sometimes|integer|exists:users,id',
            ]);

            // Find the quiz or fail
            $quiz = BodeTracNghiem::findOrFail($id);

            // Check total points if questions are provided
            if ($request->has('questions') && $request->has('total_points')) {
                $calculatedTotal = collect($request->questions)->sum('points');
                if ($calculatedTotal != $request->total_points) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tổng điểm của các câu hỏi không khớp với total_points',
                    ], 400);
                }
            }

            // Prepare update data
            $updateData = [];
            if ($request->has('title')) {
                $updateData['title'] = $request->title;
                $updateData['slug'] = Str::slug($request->title . '-' . time());
            }
            if ($request->has('hocphan_id')) {
                $updateData['hocphan_id'] = $request->hocphan_id;
            }
            if ($request->has('start_time')) {
                $updateData['start_time'] = Carbon::parse($request->start_time);
            }
            if ($request->has('end_time')) {
                $updateData['end_time'] = Carbon::parse($request->end_time);
            }
            if ($request->has('time')) {
                $updateData['time'] = $request->time;
            }
            if ($request->has('tags')) {
                $updateData['tags'] = $request->tags;
            }
            if ($request->has('user_id')) {
                $updateData['user_id'] = $request->user_id;
            }
            if ($request->has('total_points')) {
                $updateData['total_points'] = $request->total_points;
            }
            if ($request->has('questions')) {
                $updateData['questions'] = json_encode($request->questions);
            }

            // Update the quiz
            $quiz->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật bộ đề trắc nghiệm thành công',
                'data' => $quiz->fresh(), // Return updated quiz
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bộ đề trắc nghiệm',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật bộ đề trắc nghiệm: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật bộ đề tự luận
     */
    public function updateEssayQuiz(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'hocphan_id' => 'sometimes|integer|exists:hoc_phans,id',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time',
                'time' => 'sometimes|integer|min:1',
                'tags' => 'nullable|string|max:255',
                'total_points' => 'sometimes|integer|min:1',
                'questions' => 'sometimes|array|min:1',
                'questions.*.id_question' => 'required_with:questions|integer|exists:tu_luan_cauhois,id',
                'questions.*.points' => 'required_with:questions|integer|min:1',
                'user_id' => 'sometimes|integer|exists:users,id',
            ]);

            // Find the quiz or fail
            $quiz = BoDeTuLuan::findOrFail($id);

            // Check total points if questions are provided
            if ($request->has('questions') && $request->has('total_points')) {
                $calculatedTotal = collect($request->questions)->sum('points');
                if ($calculatedTotal != $request->total_points) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tổng điểm của các câu hỏi không khớp với total_points',
                    ], 400);
                }
            }

            // Prepare update data
            $updateData = [];
            if ($request->has('title')) {
                $updateData['title'] = $request->title;
                $updateData['slug'] = Str::slug($request->title . '-' . time());
            }
            if ($request->has('hocphan_id')) {
                $updateData['hocphan_id'] = $request->hocphan_id;
            }
            if ($request->has('start_time')) {
                $updateData['start_time'] = Carbon::parse($request->start_time);
            }
            if ($request->has('end_time')) {
                $updateData['end_time'] = Carbon::parse($request->end_time);
            }
            if ($request->has('time')) {
                $updateData['time'] = $request->time;
            }
            if ($request->has('tags')) {
                $updateData['tags'] = $request->tags;
            }
            if ($request->has('user_id')) {
                $updateData['user_id'] = $request->user_id;
            }
            if ($request->has('total_points')) {
                $updateData['total_points'] = $request->total_points;
            }
            if ($request->has('questions')) {
                $updateData['questions'] = json_encode($request->questions);
            }

            // Update the quiz
            $quiz->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật bộ đề tự luận thành công',
                'data' => $quiz->fresh(),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bộ đề tự luận',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật bộ đề tự luận: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a multiple-choice quiz by ID
     */
    public function showQuiz($id): JsonResponse
{
    try {
        $quiz = BodeTracNghiem::query()
            ->with([]) // Không load quan hệ nào cả
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $quiz->makeHidden(['tags']),
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bộ đề trắc nghiệm',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi tải bộ đề trắc nghiệm: ' . $e->getMessage(),
        ], 500);
    }
}


public function showEssayQuiz($id): JsonResponse
{
    try {
        $quiz = BoDeTuLuan::query()
            ->with([]) // Không load quan hệ nào cả
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $quiz->makeHidden(['tags']),
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bộ đề tự luận',
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi tải bộ đề tự luận: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Lấy danh sách câu hỏi tự luận theo học phần
     */
    public function getEssayQuestionsByHocphan(Request $request)
    {
        $request->validate([
            'hocphan_id' => 'required|integer|exists:hoc_phans,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $questions = TuluanCauhoi::where('hocphan_id', $request->hocphan_id)
                ->where('user_id', $request->user_id)
                ->get(['id', 'content', 'hocphan_id']);

            return response()->json([
                'success' => true,
                'message' => 'Danh sách câu hỏi tự luận',
                'data' => $questions,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách câu hỏi tự luận: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getTeacherQuizzes(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'hocphan_id' => 'required|integer|exists:hoc_phans,id', // Thêm hocphan_id
        ]);

        try {
            $tracNghiemQuizzes = BodeTracNghiem::where('user_id', $request->user_id)
                ->where('hocphan_id', $request->hocphan_id) // Lọc theo hocphan_id
                ->get(['id', 'title', 'hocphan_id', 'total_points', 'start_time', 'end_time', 'time'])
                ->map(function ($quiz) {
                    return array_merge($quiz->toArray(), ['type' => 'trac_nghiem']);
                });

            $tuluanQuizzes = BodeTuluan::where('user_id', $request->user_id)
                ->where('hocphan_id', $request->hocphan_id) // Lọc theo hocphan_id
                ->get(['id', 'title', 'hocphan_id', 'total_points', 'start_time', 'end_time', 'time'])
                ->map(function ($quiz) {
                    return array_merge($quiz->toArray(), ['type' => 'tu_luan']);
                });

            $quizzes = $tracNghiemQuizzes->concat($tuluanQuizzes)->sortBy('id')->values();

            return response()->json([
                'success' => true,
                'message' => 'Danh sách bộ đề của giảng viên trong học phần ' . $request->hocphan_id,
                'data' => $quizzes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách bộ đề: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Giao bộ đề cho sinh viên
     */
    public function assignQuiz(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer',
            'quiz_type' => 'required|in:trac_nghiem,tu_luan',
            // 'due_date' => 'required|date|after:now',
            'user_id' => 'required|integer|exists:users,id', // Giảng viên
        ]);

        try {
            // Lấy thông tin bộ đề
            $quiz = $request->quiz_type === 'trac_nghiem'
                ? BodeTracNghiem::where('id', $request->quiz_id)
                    ->where('user_id', $request->user_id)
                    ->first()
                : BodeTuluan::where('id', $request->quiz_id)
                    ->where('user_id', $request->user_id)
                    ->first();

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bộ đề không tồn tại hoặc không thuộc về bạn',
                ], 404);
            }

            // Lưu thông tin giao bài cho học phần
            $assignment = \App\Modules\Exercise\Models\Assignment::create([
                'quiz_id' => $request->quiz_id,
                'quiz_type' => $request->quiz_type,
                'hocphan_id' => $quiz->hocphan_id,
                'assigned_at' => now(),
                'due_date' => $request->due_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Giao bộ đề thành công cho học phần ' . $quiz->hocphan_id,
                'data' => $assignment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi giao bộ đề: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentAssignments(Request $request)
{
    $request->validate([
        'student_id' => 'required|integer|exists:students,id',
    ]);

    $studentId = $request->student_id;

    try {
        // Lấy danh sách học phần mà sinh viên đang học
        $hocPhanIds = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->where('enrollments.student_id', $studentId)
            ->pluck('phancong.hocphan_id')
            ->toArray();

        if (empty($hocPhanIds)) {
            return response()->json([
                'success' => true,
                'message' => 'Sinh viên chưa đăng ký học phần nào',
                'data' => [],
            ], 200);
        }

        // Lấy danh sách bài tập của các học phần
        $assignments = Assignment::with(['hocphan', 'quizTracNghiem', 'quizTuLuan'])
            ->whereIn('hocphan_id', $hocPhanIds)
            ->get();

        $hocphanAssignments = $assignments->groupBy('hocphan_id')->map(function ($group) use ($studentId) {
            $hocphan = $group->first()->hocphan;
            return [
                'hocphan_id' => $hocphan->id,
                'hocphan_name' => $hocphan->title ?? 'Không xác định',
                'assignments' => $group->map(function ($assignment) use ($studentId) {
                    // Kiểm tra xem sinh viên đã nộp bài chưa
                    $hasSubmitted = false;
                    if ($assignment->quiz_type === 'trac_nghiem') {
                        $hasSubmitted = TracNghiemSubmission::where('assignment_id', $assignment->id)
                            ->where('student_id', $studentId)
                            ->exists();
                    } else {
                        $hasSubmitted = TuLuanSubmission::where('assignment_id', $assignment->id)
                            ->where('student_id', $studentId)
                            ->exists();
                    }

                    // Lấy thông tin từ quizTracNghiem hoặc quizTuLuan
                    $quiz = $assignment->quiz_type === 'trac_nghiem'
                        ? $assignment->quizTracNghiem
                        : $assignment->quizTuLuan;

                    return [
                        'assignment_id' => $assignment->id,
                        'quiz_id' => $assignment->quiz_id,
                        'quiz_type' => $assignment->quiz_type,
                        'title' => $quiz ? $quiz->title : 'Không xác định',
                        'total_points' => $quiz ? $quiz->total_points : 0,
                        'time' => $quiz ? $quiz->time : 0,
                        'start_time' => $quiz && $quiz->start_time ? $quiz->start_time->toIso8601String() : null,
                        'end_time' => $quiz && $quiz->end_time ? $quiz->end_time->toIso8601String() : null,
                        'due_date' => $assignment->due_date ? $assignment->due_date->toIso8601String() : null, // Thêm due_date
                        'has_submitted' => $hasSubmitted, // Giữ has_submitted
                    ];
                })->toArray(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách bài tập theo học phần',
            'data' => $hocphanAssignments,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách bài tập: ' . $e->getMessage(),
        ], 500);
    }
}
    public function getTracNghiemQuestions(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|integer|exists:assignments,id',
        ]);

        try {
            // Lấy assignment
            $assignment = Assignment::findOrFail($request->assignment_id);

            // Kiểm tra quiz_type
            if ($assignment->quiz_type !== 'trac_nghiem') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bài tập này không phải trắc nghiệm',
                ], 400);
            }

            // Lấy bộ đề trắc nghiệm
            $quiz = BodeTracNghiem::findOrFail($assignment->quiz_id);
            $questionIds = json_decode($quiz->questions, true) ?? [];

            // Làm phẳng mảng nếu có lồng nhau
            $flatQuestionIds = is_array($questionIds) ? collect($questionIds)->flatten()->all() : [];

            if (empty($flatQuestionIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bộ đề không có câu hỏi',
                    'data' => [
                        'quiz_id' => $quiz->id,
                        'title' => $quiz->title,
                        'time' => $quiz->time,
                        'total_points' => $quiz->total_points,
                        'questions' => [],
                    ],
                ], 200);
            }

            // Lấy danh sách câu hỏi và đáp án
            $questions = TracNghiemCauhoi::whereIn('id', $flatQuestionIds)
                ->with('answers')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Danh sách câu hỏi trắc nghiệm',
                'data' => [
                    'quiz_id' => $quiz->id,
                    'title' => $quiz->title,
                    'time' => $quiz->time,
                    'total_points' => $quiz->total_points,
                    'questions' => $questions->map(fn($q) => [
                        'id' => $q->id,
                        'content' => $q->content,
                        'answers' => $q->answers->map(fn($a) => [
                            'id' => $a->id,
                            'content' => $a->content,
                            'is_correct' => $a->is_correct, // Để test, sau có thể bỏ
                        ]),
                    ]),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy assignment hoặc bộ đề với assignment_id: {$request->assignment_id}",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitTracNghiemQuiz(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'assignment_id' => 'required|integer|exists:assignments,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:trac_nghiem_cauhois,id',
            'answers.*.answer_id' => 'required|integer|exists:trac_nghiem_dapans,id',
        ]);

        try {
            $assignment = Assignment::findOrFail($request->assignment_id);

            if ($assignment->quiz_type !== 'trac_nghiem') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bài tập này không phải trắc nghiệm',
                ], 400);
            }

            $quiz = BodeTracNghiem::findOrFail($assignment->quiz_id);
            $questionIds = collect(json_decode($quiz->questions, true) ?? [])->flatten()->all();

            $submittedQuestionIds = collect($request->answers)->pluck('question_id')->all();
            $invalidQuestions = array_diff($submittedQuestionIds, $questionIds);
            if (!empty($invalidQuestions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có câu hỏi không thuộc bộ đề: ' . implode(', ', $invalidQuestions),
                ], 400);
            }

            // Lưu bài làm và lấy bản ghi mới nhất
            $submission = TracNghiemSubmission::create([
                'student_id' => $request->student_id,
                'assignment_id' => $request->assignment_id,
                'quiz_id' => $assignment->quiz_id,
                'answers' => json_encode($request->answers),
            ]);

            // Refresh để lấy submitted_at từ database
            $submission->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Nộp bài trắc nghiệm thành công',
                'data' => [
                    'submission_id' => $submission->id,
                    'submitted_at' => $submission->submitted_at
                        ? $submission->submitted_at->toIso8601String()
                        : now()->toIso8601String(), // Dự phòng nếu vẫn null
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi nộp bài: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTuLuanQuestions(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|integer|exists:assignments,id',
        ]);

        try {
            $assignment = Assignment::findOrFail($request->assignment_id);

            if ($assignment->quiz_type !== 'tu_luan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bài tập này không phải tự luận',
                ], 400);
            }

            $quiz = BodeTuluan::findOrFail($assignment->quiz_id);
            $questionIds = collect(json_decode($quiz->questions, true) ?? [])->flatten()->all();

            if (empty($questionIds)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bộ đề không có câu hỏi',
                    'data' => [
                        'quiz_id' => $quiz->id,
                        'title' => $quiz->title,
                        'time' => $quiz->time,
                        'total_points' => $quiz->total_points,
                        'questions' => [],
                    ],
                ], 200);
            }

            $questions = TuLuanCauhoi::whereIn('id', $questionIds)->get();

            return response()->json([
                'success' => true,
                'message' => 'Danh sách câu hỏi tự luận',
                'data' => [
                    'quiz_id' => $quiz->id,
                    'title' => $quiz->title,
                    'time' => $quiz->time,
                    'total_points' => $quiz->total_points,
                    'questions' => $questions->map(fn($q) => [
                        'id' => $q->id,
                        'content' => $q->content,
                    ]),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => "Không tìm thấy assignment hoặc bộ đề với assignment_id: {$request->assignment_id}",
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gửi bài làm tự luận
     */
    public function submitTuLuanQuiz(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'assignment_id' => 'required|integer|exists:assignments,id',
            'answers' => 'required|array', // [{question_id: int, content: string}]
            'answers.*.question_id' => 'required|integer|exists:tu_luan_cauhois,id',
            'answers.*.content' => 'required|string',
        ]);

        try {
            $assignment = Assignment::findOrFail($request->assignment_id);

            if ($assignment->quiz_type !== 'tu_luan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Bài tập này không phải tự luận',
                ], 400);
            }

            $quiz = BodeTuluan::findOrFail($assignment->quiz_id);
            $questionIds = collect(json_decode($quiz->questions, true) ?? [])->flatten()->all();

            $submittedQuestionIds = collect($request->answers)->pluck('question_id')->all();
            $invalidQuestions = array_diff($submittedQuestionIds, $questionIds);
            if (!empty($invalidQuestions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có câu hỏi không thuộc bộ đề: ' . implode(', ', $invalidQuestions),
                ], 400);
            }

            $submission = TuLuanSubmission::create([
                'student_id' => $request->student_id,
                'assignment_id' => $request->assignment_id,
                'quiz_id' => $assignment->quiz_id,
                'answers' => json_encode($request->answers),
            ]);

            $submission->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Nộp bài tự luận thành công',
                'data' => [
                    'submission_id' => $submission->id,
                    'submitted_at' => $submission->submitted_at
                        ? $submission->submitted_at->toIso8601String()
                        : now()->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi nộp bài: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAssignmentSubmissions(Request $request, $assignmentId)
{
    $request->merge(['assignment_id' => $assignmentId]);

    $request->validate([
        'assignment_id' => 'required|integer|exists:assignments,id',
    ]);

    try {
        $assignment = Assignment::findOrFail($request->assignment_id);
        $submissions = [];

        if ($assignment->quiz_type === 'trac_nghiem') {
            // Lấy bài nộp trắc nghiệm
            $submissions = TracNghiemSubmission::where('assignment_id', $assignment->id)
                ->with('student.user')
                ->get();

            // Tính điểm cho bài trắc nghiệm (nếu chưa có điểm)
            $quiz = BodeTracNghiem::findOrFail($assignment->quiz_id);
            $totalPoints = $quiz->total_points;
            $questionIds = collect(json_decode($quiz->questions, true) ?? [])->flatten()->all();
            $questions = TracNghiemCauhoi::whereIn('id', $questionIds)
                ->with('answers')
                ->get()
                ->keyBy('id');

            foreach ($submissions as $submission) {
                if ($submission->score === null) {
                    $answers = collect(json_decode($submission->answers, true) ?? []);
                    $correctCount = 0;

                    foreach ($answers as $answer) {
                        $questionId = $answer['question_id'];
                        $answerId = $answer['answer_id'];
                        $question = $questions->get($questionId);

                        if ($question && $question->answers->where('id', $answerId)->where('is_correct', 1)->isNotEmpty()) {
                            $correctCount++;
                        }
                    }

                    $submission->score = $totalPoints * ($correctCount / $questions->count());
                    $submission->save();
                }
            }
        } else {
            // Lấy bài nộp tự luận
            $submissions = TuLuanSubmission::where('assignment_id', $assignment->id)
                ->leftJoin('students', 'tu_luan_submissions.student_id', '=', 'students.id')
                ->leftJoin('users', 'students.user_id', '=', 'users.id')
                ->select(
                    'tu_luan_submissions.id',
                    'tu_luan_submissions.assignment_id',
                    'tu_luan_submissions.student_id',
                    'users.full_name as student_name',
                    'tu_luan_submissions.submitted_at',
                    'tu_luan_submissions.score',
                    'tu_luan_submissions.answers'
                )
                ->get();

            if ($submissions->isEmpty()) {
                Log::info('No submissions found for assignment_id: ' . $assignment->id);
            }

            // Lấy danh sách question_id từ BodeTuLuan
            $quiz = BodeTuLuan::findOrFail($assignment->quiz_id);
            $questionIds = collect(json_decode($quiz->questions, true) ?? [])->flatten()->all();
            Log::info('Question IDs from BodeTuLuan: ' . json_encode($questionIds)); // Debug

            // Lấy nội dung câu hỏi từ TuLuanCauHoi
            $questions = TuLuanCauHoi::whereIn('id', $questionIds)
                ->select('id', 'content')
                ->get()
                ->keyBy('id');
            Log::info('Questions from TuLuanCauHoi: ' . json_encode($questions)); // Debug
        }

        $result = $submissions->map(function ($submission) use ($assignment, $questions) {
            $answers = $submission->answers;
            if ($assignment->quiz_type !== 'trac_nghiem' && $answers != null) {
                // Parse answers và thêm thông tin câu hỏi
                $parsedAnswers = json_decode($answers, true) ?? [];
                Log::info('Parsed answers: ' . json_encode($parsedAnswers)); // Debug
                $answersWithQuestions = array_map(function ($answer) use ($questions) {
                    $questionId = $answer['question_id'] ?? null;
                    $question = $questions->get($questionId);
                    Log::info("Question ID: $questionId, Question: " . json_encode($question)); // Debug
                    return [
                        'question_id' => $questionId,
                        'question' => $question ? ($question->content ?? 'Không xác định') : 'Không xác định',
                        'content' => $answer['content'] ?? 'Không có đáp án',
                    ];
                }, $parsedAnswers);
                $answers = json_encode($answersWithQuestions);
            }

            return [
                'submission_id' => $submission->id,
                'student_id' => $submission->student_id,
                'student_name' => $assignment->quiz_type === 'trac_nghiem' 
                    ? ($submission->student->user->full_name ?? 'Không xác định') 
                    : ($submission->student_name ?? 'Không xác định'),
                'submitted_at' => $submission->submitted_at?->toIso8601String(),
                'score' => $submission->score !== null ? $submission->score : 'Chưa chấm',
                'answers' => $answers,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách bài nộp của sinh viên',
            'data' => [
                'assignment_id' => $assignment->id,
                'quiz_type' => $assignment->quiz_type,
                'submissions' => $result,
            ],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}
/**
 * Lấy danh sách bài tập đã giao cho học phần
 */
public function getTeacherAssignments(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'hocphan_id' => 'required|integer|exists:hoc_phans,id',
    ]);

    try {
        $assignments = Assignment::where('hocphan_id', $request->hocphan_id)
            ->with(['quizTracNghiem', 'quizTuLuan'])
            ->get();

        $result = $assignments->map(function ($assignment) {
            $quiz = $assignment->quiz_type === 'trac_nghiem' ? $assignment->quizTracNghiem : $assignment->quizTuLuan;
            return [
                'assignment_id' => $assignment->id,
                'quiz_id' => $assignment->quiz_id ?? 0, // Mặc định 0 nếu null
                'quiz_type' => $assignment->quiz_type,
                'title' => $quiz ? $quiz->title : 'Không xác định',
                'total_points' => $quiz ? (int)$quiz->total_points : 0,
                'time' => $quiz ? (int)$quiz->time : 0,
                'assigned_at' => $assignment->assigned_at ? $assignment->assigned_at->toIso8601String() : null,
                'due_date' => $assignment->due_date ? $assignment->due_date->toIso8601String() : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách bài tập đã giao',
            'data' => $result,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}

public function deleteQuiz(Request $request)
{
    $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'quiz_id' => 'required|integer',
        'quiz_type' => 'required|in:trac_nghiem,tu_luan',
    ]);

    try {
        // Kiểm tra quyền: user_id phải là giảng viên
        $user = User::findOrFail($request->user_id);
        if ($user->role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ giảng viên mới có quyền xóa bộ đề',
            ], 403);
        }

        // Kiểm tra xem bộ đề đã được giao bài chưa
        $assignmentExists = Assignment::where('quiz_id', $request->quiz_id)
            ->where('quiz_type', $request->quiz_type)
            ->exists();

        if ($assignmentExists) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa bộ đề vì đã được giao bài',
            ], 400);
        }

        // Xóa bộ đề dựa trên quiz_type
        if ($request->quiz_type === 'trac_nghiem') {
            $quiz = BodeTracNghiem::find($request->quiz_id);
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bộ đề trắc nghiệm không tồn tại',
                ], 404);
            }
            $quiz->delete();
        } else {
            $quiz = BodeTuluan::find($request->quiz_id);
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bộ đề tự luận không tồn tại',
                ], 404);
            }
            $quiz->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa bộ đề thành công',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}

public function deleteAssignment(Request $request)
{
    try {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'assignment_id' => 'required|integer|exists:assignments,id',
        ]);

        // Bỏ kiểm tra quyền của user
        // Chỉ kiểm tra xem bài tập có tồn tại không
        $assignment = Assignment::find($data['assignment_id']);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Bài tập không tồn tại'], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bài tập thành công',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi xóa bài tập: ' . $e->getMessage(),
        ], 500);
    }
}

public function updateSubmissionScore(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $data = $request->validate([
                'user_id' => 'required|integer',
                'submission_id' => 'required|integer|exists:tu_luan_submissions,id',
                'score' => 'required|numeric|min:0|max:10',
            ]);

            // Kiểm tra quyền (chỉ giảng viên được chấm điểm)
            $teacher = User::find($data['user_id']);
            if (!$teacher || $teacher->role !== 'teacher') {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền chấm điểm'], 403);
            }

            // Tìm bài nộp tự luận
            $submission = TuLuanSubmission::find($data['submission_id']);
            if (!$submission) {
                return response()->json(['success' => false, 'message' => 'Bài nộp tự luận không tồn tại'], 404);
            }

            // Cập nhật điểm số
            $submission->score = $data['score'];
            $submission->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật điểm số thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật điểm số: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentAverageScore(Request $request, $hocphanId)
{
    $request->merge([
        'hocphan_id' => $hocphanId,
    ]);

    $request->validate([
        'hocphan_id' => 'required|integer|exists:hoc_phans,id',
    ]);

    try {
        // Lấy danh sách bài tập thuộc học phần (dựa trên hocphan_id)
        $assignments = Assignment::where('hocphan_id', $hocphanId)->get();
        if ($assignments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bài tập nào trong học phần này',
            ], 404);
        }

        // Lấy danh sách sinh viên trong học phần, bao gồm mssv
        $students = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select(
                'students.id as student_id',
                'students.mssv as mssv', // Thêm mssv
                'users.full_name as student_name'
            )
            ->where('phancong.hocphan_id', $hocphanId)
            ->distinct()
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có sinh viên nào trong học phần này',
            ], 404);
        }

        // Lấy danh sách bộ đề để ánh xạ assignment_id với tên bài tập
        $assignmentDetails = [];
        foreach ($assignments as $assignment) {
            $quizTitle = null;
            if ($assignment->quiz_type === 'trac_nghiem') {
                $quiz = DB::table('bode_tracnghiems')
                    ->where('id', $assignment->quiz_id)
                    ->select('title')
                    ->first();
                $quizTitle = $quiz ? $quiz->title : 'Bài tập trắc nghiệm không xác định';
            } else {
                $quiz = DB::table('bode_tuluans')
                    ->where('id', $assignment->quiz_id)
                    ->select('title')
                    ->first();
                $quizTitle = $quiz ? $quiz->title : 'Bài tập tự luận không xác định';
            }

            $assignmentDetails[$assignment->id] = [
                'quiz_type' => $assignment->quiz_type,
                'title' => $quizTitle,
            ];
        }

        $studentAverages = [];

        foreach ($students as $student) {
            $submissions = [];
            $totalScore = 0;
            $scoredCount = 0;

            foreach ($assignments as $assignment) {
                $submission = null;

                if ($assignment->quiz_type === 'trac_nghiem') {
                    $submission = TracNghiemSubmission::where('assignment_id', $assignment->id)
                        ->where('student_id', $student->student_id)
                        ->first();
                } else {
                    $submission = TuLuanSubmission::where('assignment_id', $assignment->id)
                        ->where('student_id', $student->student_id)
                        ->first();
                }

                if ($submission && $submission->score !== null) {
                    $submissions[] = [
                        'assignment_title' => $assignmentDetails[$assignment->id]['title'], // Thay assignment_id bằng tên bài tập
                        'score' => $submission->score,
                    ];
                    $totalScore += $submission->score;
                    $scoredCount++;
                }
            }

            $averageScore = $scoredCount > 0 ? $totalScore / $scoredCount : null;

            $studentAverages[] = [
                'mssv' => $student->mssv, // Thay student_id bằng mssv
                'student_name' => $student->student_name,
                'average_score' => $averageScore != null ? round($averageScore, 2) : null,
                'submissions' => $submissions,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Điểm trung bình của tất cả sinh viên trong học phần',
            'data' => [
                'hocphan_id' => (int) $hocphanId,
                'students' => $studentAverages,
            ],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Xóa câu hỏi trắc nghiệm
 *
 * @param Request $request
 * @param int $id ID của câu hỏi trắc nghiệm
 * @return \Illuminate\Http\JsonResponse
 */
public function deleteQuestion(Request $request, $id)
{
    try {
        // Tìm câu hỏi trắc nghiệm theo ID
        $question = TracNghiemCauhoi::find($id);

        // Kiểm tra xem câu hỏi có tồn tại không
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy câu hỏi trắc nghiệm với ID này',
            ], 404);
        }

        // Xóa câu hỏi
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa câu hỏi trắc nghiệm thành công',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi xóa câu hỏi trắc nghiệm: ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Xóa câu hỏi tự luận
 *
 * @param Request $request
 * @param int $id ID của câu hỏi tự luận
 * @return \Illuminate\Http\JsonResponse
 */
public function deleteEssayQuestion(Request $request, $id)
{
    try {
        // Tìm câu hỏi tự luận theo ID
        $question = TuLuanCauHoi::find($id);

        // Kiểm tra xem câu hỏi có tồn tại không
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy câu hỏi tự luận với ID này',
            ], 404);
        }

        // Xóa câu hỏi
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa câu hỏi tự luận thành công',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi xóa câu hỏi tự luận: ' . $e->getMessage(),
        ], 500);
    }
}
}
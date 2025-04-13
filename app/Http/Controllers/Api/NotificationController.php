<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Modules\Exercise\Models\NoidungPhancong;
use App\Modules\Teaching_1\Models\ClassModel;
use App\Modules\Teaching_1\Models\Student as ModelsStudent;
use App\Modules\Teaching_2\Models\PhanCong;
use app\Modules\Teaching_3\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Hàm gửi thông báo - dành cho giảng viên
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:teacher,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // Giới hạn file 10MB
        ]);

        $teacherId = $request->teacher_id;

        // Kiểm tra xem giảng viên có phải là giáo viên của lớp này không
        $class = ClassModel::where('id', $request->class_id)->first();

        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin lớp học'
            ], 404);
        }

        // Lưu file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('notifications', $fileName, 'public');

        // Kiểm tra xem file có được lưu thành công không
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu file'
            ], 500);
        }

        // Tạo thông báo mới
        $notification = Notification::create([
            'class_id' => $request->class_id,
            'teacher_id' => $teacherId,
            'title' => $request->title,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gửi thông báo thành công',
            'data' => $notification
        ], 201);
    }

    /**
     * Lấy danh sách thông báo cho sinh viên - chỉ lấy thông báo thuộc lớp của sinh viên
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentNotifications(Request $request)
    {
        try {
            // Validate student_id từ body
            $request->validate([
                'student_id' => 'required|integer|exists:students,id',
            ]);

            // Lấy student_id từ body
            $studentId = $request->input('student_id');

            // Lấy thông tin sinh viên dựa trên cột id
            $student = ModelsStudent::where('id', $studentId)->first();

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin sinh viên',
                    'debug' => ['student_id' => $studentId]
                ], 404);
            }

            $classId = $student->class_id;

            if (!$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinh viên chưa được xếp lớp',
                    'debug' => ['student_id' => $student->id, 'class_id' => $classId]
                ], 404);
            }

            // Kiểm tra xem có thông báo nào cho lớp này không
            $notificationCount = Notification::where('class_id', $classId)->count();

            if ($notificationCount == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có thông báo nào cho lớp của sinh viên',
                    'data' => [],
                    'debug' => ['class_id' => $classId, 'notification_count' => 0]
                ]);
            }

            // Lấy các thông báo thuộc về lớp của sinh viên
            $notifications = Notification::where('class_id', $classId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Thêm URL tương đối để tải file và thông tin giảng viên
            $notifications = $notifications->map(function($notification) {
                // Kiểm tra xem file có tồn tại không
                if (Storage::disk('public')->exists($notification->file_path)) {
                    // Chỉ trả về đường dẫn tương đối
                    $notification->file_url = 'storage/' . $notification->file_path;
                } else {
                    $notification->file_url = null;
                }

                // Lấy thông tin giảng viên từ bảng users thông qua teacher
                if ($notification->teacher_id) {
                    $teacher = \App\Modules\Teaching_1\Models\Teacher::where('id', $notification->teacher_id)->first();
                    if ($teacher) {
                        $user = \App\Models\User::where('id', $teacher->user_id)->first();
                        $notification->teacher_name = $user ? $user->full_name : 'Không xác định';
                    } else {
                        $notification->teacher_name = 'Không xác định';
                    }
                } else {
                    $notification->teacher_name = 'Không xác định';
                }

                return $notification;
            });

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'count' => $notifications->count(),
                'debug' => [
                    'class_id' => $classId,
                    'student_id' => $student->id,
                    'notification_count' => $notificationCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy thông báo',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function uploadTeachingContent(Request $request)
    {
        // Validate dữ liệu đầu vào
        $request->validate([
            'phancong_id' => 'required|exists:phancong,id',
            'teacher_id' => 'required|exists:teacher,id',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // Giới hạn file 10MB
        ]);

        $teacherId = $request->teacher_id;
        $phancongId = $request->phancong_id;

        // Kiểm tra xem giảng viên có được phân công dạy học phần này không
        $phancong = PhanCong::where('id', $phancongId)
            ->where('giangvien_id', $teacherId)
            ->first();

        if (!$phancong) {
            return response()->json([
                'success' => false,
                'message' => 'Giảng viên không được phân công dạy học phần này'
            ], 403);
        }

        // Lưu file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('teaching_resources', $fileName, 'public');

        // Kiểm tra xem file có được lưu thành công không
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu file'
            ], 500);
        }

        // Tạo slug từ title
        $slug = Str::slug($request->title);

        // Tạo nội dung giảng dạy mới
        $teachingContent = NoidungPhancong::create([
            'phancong_id' => $phancongId,
            'title' => $request->title,
            'slug' => $slug,
            'resources' => $filePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tải lên nội dung giảng dạy thành công',
            'data' => $teachingContent
        ], 201);
    }

    /**
 * API để sinh viên lấy danh sách nội dung giảng dạy (bao gồm file học tập)
 *
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function getTeachingContentForStudent(Request $request)
{
    try {
        // Validate dữ liệu đầu vào
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'phancong_id' => 'required|integer|exists:phancong,id',
        ]);

        $studentId = $request->student_id;
        $phancongId = $request->phancong_id;

        // Kiểm tra xem sinh viên có thuộc học phần này không
        $enrollment = DB::table('enrollments')
        ->where('student_id', $request->student_id)
        ->where('phancong_id', $request->phancong_id)
        ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Sinh viên không thuộc học phần này',
            ], 403);
        }

        // Lấy danh sách nội dung giảng dạy
        $teachingContents = NoidungPhancong::where('phancong_id', $phancongId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($teachingContents->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Không có nội dung giảng dạy nào',
                'data' => [],
            ]);
        }

        // Thêm đường dẫn tương đối cho file
        $teachingContents = $teachingContents->map(function ($content) {
            if (Storage::disk('public')->exists($content->resources)) {
                $content->file_url = 'storage/' . $content->resources;
            } else {
                $content->file_url = null;
            }

            // Lấy thông tin giảng viên từ bảng phancong
            $phancong = PhanCong::find($content->phancong_id);
            if ($phancong && $phancong->giangvien_id) {
                $teacher = \App\Modules\Teaching_1\Models\Teacher::where('id', $phancong->giangvien_id)->first();
                if ($teacher) {
                    $user = \App\Models\User::where('id', $teacher->user_id)->first();
                    $content->teacher_name = $user ? $user->full_name : 'Không xác định';
                } else {
                    $content->teacher_name = 'Không xác định';
                }
            } else {
                $content->teacher_name = 'Không xác định';
            }

            return $content;
        });

        return response()->json([
            'success' => true,
            'data' => $teachingContents,
            'count' => $teachingContents->count(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy nội dung giảng dạy',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

public function getTeachingContentForTeacher(Request $request)
{
    try {
        // Validate dữ liệu đầu vào
        $request->validate([
            'teacher_id' => 'required|integer|exists:teacher,id',
            'phancong_id' => 'required|integer|exists:phancong,id',
        ]);

        $teacherId = $request->teacher_id;
        $phancongId = $request->phancong_id;

        // Kiểm tra xem giảng viên có phụ trách học phần này không
        $phancong = PhanCong::where('id', $phancongId)
            ->where('giangvien_id', $teacherId)
            ->first();

        if (!$phancong) {
            return response()->json([
                'success' => false,
                'message' => 'Giảng viên không phụ trách học phần này',
            ], 403);
        }

        // Lấy danh sách nội dung giảng dạy
        $teachingContents = NoidungPhancong::where('phancong_id', $phancongId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($teachingContents->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Không có nội dung giảng dạy nào',
                'data' => [],
            ]);
        }

        // Thêm đường dẫn tương đối cho file
        $teachingContents = $teachingContents->map(function ($content) {
            if (Storage::disk('public')->exists($content->resources)) {
                $content->file_url = 'storage/' . $content->resources;
            } else {
                $content->file_url = null;
            }

            // Lấy thông tin giảng viên từ bảng phancong (để hiển thị tên giảng viên, mặc dù đây là chính giảng viên đang truy cập)
            $phancong = PhanCong::find($content->phancong_id);
            if ($phancong && $phancong->giangvien_id) {
                $teacher = \App\Modules\Teaching_1\Models\Teacher::where('id', $phancong->giangvien_id)->first();
                if ($teacher) {
                    $user = \App\Models\User::where('id', $teacher->user_id)->first();
                    $content->teacher_name = $user ? $user->full_name : 'Không xác định';
                } else {
                    $content->teacher_name = 'Không xác định';
                }
            } else {
                $content->teacher_name = 'Không xác định';
            }

            return $content;
        });

        return response()->json([
            'success' => true,
            'data' => $teachingContents,
            'count' => $teachingContents->count(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Đã xảy ra lỗi khi lấy nội dung giảng dạy',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Teaching_3\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{

    public function getAvailableCourses(Request $request)
{
    if (!$request->student_id) {
        return response()->json(['message' => 'Missing student_id'], 400);
    }

    $studentMajor = DB::table('students')
        ->join('nganh', 'students.nganh_id', '=', 'nganh.id')
        ->where('students.id', $request->student_id)
        ->value('nganh.id');

    if (!$studentMajor) {
        return response()->json(['message' => 'Student major not found'], 404);
    }

    $courses = [];
    $ctdts = DB::select("
        SELECT a.id FROM chuong_trinh_dao_tao a
        JOIN program_details b ON a.id = b.chuongtrinh_id
        WHERE a.nganh_id = ?", [$studentMajor]);

    if (count($ctdts) > 0) {
        $ctdt = $ctdts[0];

        $rawCourses = DB::select("
            SELECT 
                d.id AS phancong_id,
                h.title,
                h.code,
                h.tinchi,
                COALESCE(b.class_name, 'Không có lớp') AS class_course,
                d.max_student,
                COALESCE(f.full_name, 'Chưa có giảng viên') AS teacher_name,
                COALESCE(g.so_hoc_ky, 'Không xác định') AS so_hoc_ky,
                d.loai
            FROM (
                SELECT a.*, c.loai, c.hoc_ky_id
                FROM phancong a
                JOIN (
                    SELECT hocphan_id, loai, hoc_ky_id FROM program_details 
                    WHERE chuongtrinh_id = ?
                ) AS c ON a.hocphan_id = c.hocphan_id
            ) AS d
            LEFT JOIN classes b ON d.class_id = b.id
            LEFT JOIN (SELECT users.full_name, teacher.id FROM teacher
                LEFT JOIN users ON teacher.user_id = users.id) AS f ON d.giangvien_id = f.id
            LEFT JOIN hoc_ky g ON d.hoc_ky_id = g.id
            LEFT JOIN hoc_phans h ON d.hocphan_id = h.id", [$ctdt->id]);

        $courses = array_map(function ($course) {
            return [
                "phancong_id"  => $course->phancong_id,
                "title"        => $course->title,
                "code"         => $course->code,
                "tinchi"       => $course->tinchi,
                "class_course" => $course->class_course,
                "max_student"  => $course->max_student,
                "teacher_name" => $course->teacher_name,
                "so_hoc_ky"    => $course->so_hoc_ky,
                "loai"         => $course->loai,
            ];
        }, $rawCourses);
    } else {
        return response()->json(['message' => 'Không tìm thấy dữ liệu'], 200);
    }

    return response()->json($courses);
}





public function enrollCourse(Request $request)
{
    // Kiểm tra request có đầy đủ thông tin không
    if (!$request->phancong_id || !$request->student_id) {
        return response()->json(['message' => 'Missing phancong_id or student_id'], 400);
    }

    // Kiểm tra nếu sinh viên đã đăng ký học phần này
    $existingEnrollment = DB::table('enrollments')
        ->where('student_id', $request->student_id)
        ->where('phancong_id', $request->phancong_id)
        ->first();

    if ($existingEnrollment) {
        return response()->json(['message' => 'Học phần này đã đăng kí'], 400);
    }

    // Lấy thông tin phân công học phần
    $phancong = DB::table('phancong')->where('id', (int) $request->phancong_id)->first();

    Log::info('phancong_id received: ' . $request->phancong_id);

    // Kiểm tra nếu không tìm thấy học phần
    if (!$phancong) {
        return response()->json(['message' => 'Course assignment not found'], 404);
    }

    // Kiểm tra số lượng sinh viên đã đăng ký
    $currentEnrollments = DB::table('enrollments')->where('phancong_id', $request->phancong_id)->count();
    if ($currentEnrollments >= $phancong->max_student) {
        return response()->json(['message' => 'Course is full'], 400);
    }

    // Lấy điều kiện tiên quyết từ bảng program_details
    $prerequisiteData = DB::table('program_details')
        ->where('hocphan_id', $phancong->hocphan_id)
        ->value('hocphantienquyet');

    if ($prerequisiteData) {
        $prerequisiteArray = json_decode($prerequisiteData, true);
        if (isset($prerequisiteArray['next']) && is_array($prerequisiteArray['next'])) {
            $requiredCourses = $prerequisiteArray['next'];

            // Lấy danh sách học phần sinh viên đã hoàn thành
            $completedCourses = DB::table('enrollments')
                ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
                ->where('enrollments.student_id', $request->student_id)
                ->where('enrollments.status', 'finished')
                ->pluck('phancong.hocphan_id')
                ->toArray();

            // Xác định các học phần chưa hoàn thành
            $missingCourses = array_diff($requiredCourses, $completedCourses);

            if (!empty($missingCourses)) {
                // Lấy tên của các học phần còn thiếu
                $missingCourseNames = DB::table('hoc_phans')
                    ->whereIn('id', $missingCourses)
                    ->pluck('title')
                    ->toArray();

                return response()->json([
                    'message' => 'Prerequisites not met',
                    'missing_courses' => $missingCourseNames,
                ], 400);
            }
        }
    }

    // Đăng ký học phần
    DB::table('enrollments')->insert([
        'student_id' => $request->student_id,
        'phancong_id' => $request->phancong_id,
        'timespending' => $request->timespending ?? 0,
        'process' => $request->process ?? 0,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['message' => 'Enrollment successful'], 201);
}

public function getEnrolledCourses(Request $request)
{
    if (!$request->student_id) {
        return response()->json(['message' => 'Missing student_id'], 400);
    }

    $courses = DB::table('enrollments')
        ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
        ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
        ->join('classes', 'phancong.class_id', '=', 'classes.id')
        ->join('teacher', 'phancong.giangvien_id', '=', 'teacher.id')
        ->join('users', 'teacher.user_id', '=', 'users.id')
        ->select(
            'enrollments.id as enrollment_id',
            'phancong.id as phancong_id',
            'hoc_phans.id as hocphan_id',
            'hoc_phans.title as title',
            'hoc_phans.tinchi as tinchi',
            'hoc_phans.code as course_code',
            'classes.class_name as class_course',
            'users.full_name as teacher_name',
            'enrollments.status',
            'enrollments.created_at'
        )
        ->where('enrollments.student_id', $request->student_id)
        ->get();

    return response()->json($courses);
}

public function deleteEnrollment(Request $request)
{
    if (!$request->enrollment_id) {
        return response()->json(['message' => 'Missing enrollment_id'], 400);
    }

    $deleted = DB::table('enrollments')
        ->where('id', $request->enrollment_id)
        ->delete();

    if ($deleted) {
        return response()->json(['message' => 'Enrollment deleted successfully'], 200);
    } else {
        return response()->json(['message' => 'Enrollment not found or already deleted'], 404);
    }
}

public function searchCourses(Request $request)
{
    // Kiểm tra student_id có được gửi trong request không
    if (!$request->student_id) {
        return response()->json(['message' => 'Missing student_id'], 400);
    }

    // Lấy ngành của sinh viên
    $studentMajor = DB::table('students')
        ->join('nganh', 'students.nganh_id', '=', 'nganh.id')
        ->where('students.id', $request->student_id)
        ->value('nganh.id');

    if (!$studentMajor) {
        return response()->json(['message' => 'Student major not found'], 404);
    }

    // Lấy từ khóa tìm kiếm từ request
    $keyword = $request->keyword;

    // Lấy các học phần liên quan đến ngành của sinh viên, có lọc theo keyword
    $courses = DB::table('phancong')
        ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
        ->join('classes', 'phancong.class_id', '=', 'classes.id')
        ->join('program_details', 'phancong.hocphan_id', '=', 'program_details.hocphan_id')
        ->join('chuong_trinh_dao_tao', 'program_details.chuongtrinh_id', '=', 'chuong_trinh_dao_tao.id')
        ->join('nganh', 'chuong_trinh_dao_tao.nganh_id', '=', 'nganh.id')
        ->join('teacher', 'phancong.giangvien_id', '=', 'teacher.id')
        ->join('users', 'teacher.user_id', '=', 'users.id')
        ->join('hoc_ky', 'program_details.hoc_ky_id', '=', 'hoc_ky.id')
        ->select(
            'phancong.id as phancong_id',
            'hoc_phans.title',
            'hoc_phans.code',
            'hoc_phans.tinchi',
            'classes.class_name as class_course',
            'phancong.max_student',
            'users.full_name as teacher_name',
            'hoc_ky.so_hoc_ky',
            'program_details.loai'
        )
        ->where('nganh.id', $studentMajor)
        ->when($keyword, function ($query, $keyword) {
            // Tìm kiếm theo tên học phần, mã học phần hoặc tên giảng viên
            $query->where('hoc_phans.title', 'like', "%$keyword%")
                  ->orWhere('hoc_phans.code', 'like', "%$keyword%");
        })
        ->get();

    return response()->json($courses);
}


// Thời khoá biểu
public function getTimetable(Request $request)
{
    if (!$request->student_id) {
        return response()->json(['message' => 'Missing student_id'], 400);
    }

    try {
        // Truy vấn thời khóa biểu với các thông tin liên quan
        $timetable = DB::table('thoi_khoa_bieus')
            ->join('phancong', 'thoi_khoa_bieus.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('enrollments', 'phancong.id', '=', 'enrollments.phancong_id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('classes', 'phancong.class_id', '=', 'classes.id')
            ->join('dia_diem', 'thoi_khoa_bieus.diadiem_id', '=', 'dia_diem.id')
            ->join('teacher', 'phancong.giangvien_id', '=', 'teacher.id')
            ->join('users', 'teacher.user_id', '=', 'users.id')
            ->select(
                'thoi_khoa_bieus.id as timetable_id',
                'hoc_phans.title',
                'thoi_khoa_bieus.buoi',
                'thoi_khoa_bieus.ngay',
                'thoi_khoa_bieus.tietdau',
                'thoi_khoa_bieus.tietcuoi',
                'dia_diem.title as location',
                'classes.class_name as class_course',
                'users.full_name as teacher_name'
            )
            ->where('students.id', $request->student_id)
            ->orderBy('thoi_khoa_bieus.ngay', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách thời khóa biểu',
            'data' => $timetable
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy thời khóa biểu: ' . $e->getMessage()
        ], 500);
    }
}

public function getTeacherSchedule(Request $request)
{
    if (!$request->teacher_id) {
        return response()->json(['message' => 'Missing teacher_id'], 400);
    }

    try {
        // Truy vấn thời khóa biểu của giảng viên
        $schedule = DB::table('thoi_khoa_bieus')
            ->join('phancong', 'thoi_khoa_bieus.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('classes', 'phancong.class_id', '=', 'classes.id')
            ->join('dia_diem', 'thoi_khoa_bieus.diadiem_id', '=', 'dia_diem.id')
            ->join('teacher', 'phancong.giangvien_id', '=', 'teacher.id')
            ->join('users', 'teacher.user_id', '=', 'users.id')
            ->select(
                'thoi_khoa_bieus.id as timetable_id',
                'phancong.id as phancong_id',
                'hoc_phans.title as subject',
                'phancong.hocphan_id as hocphan_id',
                'thoi_khoa_bieus.buoi',
                'thoi_khoa_bieus.ngay',
                'thoi_khoa_bieus.tietdau',
                'thoi_khoa_bieus.tietcuoi',
                'dia_diem.title as location',
                'classes.class_name as class_course',
                'users.full_name as teacher_name'
            )
            ->where('teacher.id', $request->teacher_id)
            ->orderBy('thoi_khoa_bieus.ngay', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách lịch dạy của giảng viên',
            'data' => $schedule
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch dạy: ' . $e->getMessage()
        ], 500);
    }
}


public function getStudentExamSchedules(Request $request)
{
    if (!$request->student_id) {
        return response()->json(['message' => 'Missing student_id'], 400);
    }

    try {
        // Lấy danh sách lịch thi chỉ của các học phần mà sinh viên đã đăng ký
        $examSchedules = DB::table('lich_thi')
            ->join('phancong', 'lich_thi.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('enrollments', 'phancong.id', '=', 'enrollments.phancong_id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('classes', 'phancong.class_id', '=', 'classes.id')
            ->select(
                'lich_thi.id as exam_id',
                'hoc_phans.title as subject',
                'lich_thi.buoi',
                'lich_thi.ngay1 as exam_date',
                'lich_thi.ngay2 as backup_exam_date',
                'lich_thi.dia_diem_thi as location',
                'classes.class_name as class_course'
            )
            ->where('students.id', $request->student_id)
            ->orderBy('lich_thi.ngay1', 'asc')
            ->get();

        // Xử lý dữ liệu để giải mã JSON trong cột location
        $examSchedules->transform(function ($exam) {
            $locationData = json_decode($exam->location, true);
            $exam->location = isset($locationData['location']) ? implode(', ', $locationData['location']) : null;
            return $exam;
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách lịch thi',
            'data' => $examSchedules
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy lịch thi: ' . $e->getMessage()
        ], 500);
    }
}


public function getClassStudents(Request $request)
    {
        $teacherId = $request->input('teacher_id');

        $students = DB::table('students')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->join('teacher', 'classes.teacher_id', '=', 'teacher.id')
            ->join('users', 'students.user_id', '=', 'users.id')  // Join để lấy tên sinh viên
            ->select(
                'students.id as student_id',
                'students.mssv',
                'users.full_name as student_name',
                'classes.class_name',
                'classes.description',
                'students.khoa',
                'students.status'
            )
            ->where('classes.teacher_id', $teacherId)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $students
        ], 200);
    }


    public function getStudentCourses(Request $request)
{
    $studentId = $request->input('student_id');  // Thêm tham số student_id để lọc theo sinh viên
    
    // Lấy danh sách học phần sinh viên đã đăng ký trong lớp của giảng viên
    $studentCourses = DB::table('enrollments')
        ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
        ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
        ->join('students', 'enrollments.student_id', '=', 'students.id')
        ->join('classes', 'students.class_id', '=', 'classes.id')
        ->join('teacher', 'classes.teacher_id', '=', 'teacher.id')
        ->join('users', 'students.user_id', '=', 'users.id')
        ->select(
            'students.id as student_id',
            'users.full_name as student_name',
            'students.mssv',
            'hoc_phans.title as course_title',
            'hoc_phans.code as course_code',
            'hoc_phans.tinchi as credits',
            'classes.class_name as class_course',
            'enrollments.status as enrollment_status',
            'enrollments.created_at as enrollment_date'
        )
        ->where('students.id', $studentId)  // Lọc theo student_id
        ->orderBy('enrollments.created_at', 'desc')
        ->get();
    
    if ($studentCourses->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Không tìm thấy học phần nào cho sinh viên này.'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data' => $studentCourses
    ], 200);
}

public function getStudentsByTeacher(Request $request)
{
    if (!$request->teacher_id) {
        return response()->json(['message' => 'Missing teacher_id'], 400);
    }

    if (!$request->phancong_id) {
        return response()->json(['message' => 'Missing phancong_id'], 400);
    }

    try {
        // Truy vấn danh sách sinh viên
        $students = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('classes', 'students.class_id', '=', 'classes.id')
            ->select(
                'students.id as student_id',
                'users.full_name as student_name',
                'users.email as student_email',
                'hoc_phans.title as subject',
                'hoc_phans.id as hocphan_id',
                'hoc_phans.code as subject_code',
                'classes.class_name as class_name'
            )
            ->where('phancong.giangvien_id', $request->teacher_id)
            ->where('phancong.id', $request->phancong_id)
            ->orderBy('students.id', 'asc')
            ->get();

        // Lấy danh sách tkb_id từ thoi_khoa_bieus cho phancong_id này
        $tkbIds = DB::table('thoi_khoa_bieus')
            ->where('phancong_id', $request->phancong_id)
            ->pluck('id')
            ->toArray();

        // Đếm số buổi vắng cho từng sinh viên
        $attendanceData = DB::table('attendances')
            ->whereIn('tkb_id', $tkbIds)
            ->whereNotNull('student_list') // Chỉ lấy các phiên đã có dữ liệu
            ->get()
            ->map(function ($attendance) {
                $studentList = json_decode($attendance->student_list, true);
                return $studentList['absent'] ?? [];
            })
            ->flatten()
            ->countBy()
            ->all();

        // Thêm số buổi vắng vào danh sách sinh viên
        $students = $students->map(function ($student) use ($attendanceData) {
            $student->absent_count = $attendanceData[$student->student_id] ?? 0;
            return $student;
        });

        return response()->json([
            'success' => true,
            'message' => 'Danh sách sinh viên theo giảng viên và học phần',
            'data' => $students
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách sinh viên: ' . $e->getMessage()
        ], 500);
    }
}

public function updateEnrollmentStatus(Request $request)
{
    try {
        // Validate dữ liệu đầu vào
        $data = $request->validate([
            'user_id' => 'required|integer',
            'enrollment_id' => 'required|integer|exists:enrollments,id',
            'status' => 'required|string|in:pending,success,finished,rejected', // Các trạng thái hợp lệ
        ]);

        // Kiểm tra quyền (giả định chỉ giảng viên mới được thay đổi trạng thái)
        $teacher = User::find($data['user_id']);
        if (!$teacher || $teacher->role !== 'teacher') {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền thay đổi trạng thái'], 403);
        }

        // Tìm bản ghi enrollment
        $enrollment = Enrollment::find($data['enrollment_id']);
        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Bản ghi đăng ký không tồn tại'], 404);
        }

        // Cập nhật trạng thái
        $enrollment->status = $data['status'];
        $enrollment->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage(),
        ], 500);
    }
}


public function updateStudentScores(Request $request, $studentId, $hocphanId)
{
    // Validate dữ liệu đầu vào
    $request->validate([
        'DiemBP' => 'required|numeric|min:0|max:10',
        'Thi1' => 'nullable|numeric|min:0|max:10',
        'Thi2' => 'nullable|numeric|min:0|max:10',
    ]);

    try {
        // Lấy dữ liệu từ request
        $DiemBP = $request->input('DiemBP');
        $Thi1 = $request->input('Thi1');
        $Thi2 = $request->input('Thi2');

        // Tính toán điểm
        $Diem1 = null;
        $Diem2 = null;
        $DiemMax = null;
        $DiemChu = null;
        $DiemHeSo4 = null;

        // Tính Diem1 nếu có Thi1
        if ($Thi1 !== null) {
            $Diem1 = round(($DiemBP * 0.3) + ($Thi1 * 0.7), 1);
        }

        // Tính Diem2 nếu có Thi2
        if ($Thi2 !== null) {
            $Diem2 = round(($DiemBP * 0.3) + ($Thi2 * 0.7), 1);
        }

        // Tính DiemMax
        if ($Diem1 !== null && $Diem2 !== null) {
            $DiemMax = max($Diem1, $Diem2);
        } elseif ($Diem1 !== null) {
            $DiemMax = $Diem1;
        } elseif ($Diem2 !== null) {
            $DiemMax = $Diem2;
        }

        // Kiểm tra xem học phần có phải là học phần điều kiện không
        $isConditionCourse = DB::table('hoc_phans')
            ->where('id', $hocphanId)
            ->value('is_condition_course');

        // Tính DiemChu và DiemHeSo4
        if ($DiemMax !== null) {
            if ($isConditionCourse) {
                // Nếu là học phần điều kiện
                if ($DiemMax >= 5.0) {
                    $DiemChu = 'P'; // Đạt
                    $DiemHeSo4 = null; // Không tính vào GPA
                } else {
                    $DiemChu = 'F'; // Không đạt
                    $DiemHeSo4 = null; // Không tính vào GPA
                }
            } else {
                // Nếu là học phần bình thường, tính như cũ
                list($DiemChu, $DiemHeSo4) = $this->calculateGrade($DiemMax);
            }
        }

        // Tìm enroll_id dựa trên student_id và hocphan_id
        $enrollId = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->where('enrollments.student_id', $studentId)
            ->where('phancong.hocphan_id', $hocphanId)
            ->value('enrollments.id');

        if (!$enrollId) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy bản ghi đăng ký học phần cho sinh viên này',
            ], 404);
        }

        // Cập nhật hoặc thêm mới vào cơ sở dữ liệu
        DB::table('enroll_results')
            ->updateOrInsert(
                [
                    'enroll_id' => $enrollId,
                ],
                [
                    'student_id' => $studentId,
                    'DiemBP' => $DiemBP,
                    'Thi1' => $Thi1,
                    'Diem1' => $Diem1,
                    'Thi2' => $Thi2,
                    'Diem2' => $Diem2,
                    'DiemMax' => $DiemMax,
                    'DiemChu' => $DiemChu,
                    'DiemHeSo4' => $DiemHeSo4,
                    'updated_at' => now(),
                ]
            );

        // Trả về response
        return response()->json([
            'success' => true,
            'message' => 'Điểm đã được cập nhật và tính toán thành công',
            'data' => [
                'DiemBP' => $DiemBP,
                'Thi1' => $Thi1,
                'Diem1' => $Diem1,
                'Thi2' => $Thi2,
                'Diem2' => $Diem2,
                'DiemMax' => $DiemMax,
                'DiemChu' => $DiemChu,
                'DiemHeSo4' => $DiemHeSo4,
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
 * Tính điểm chữ và điểm hệ số 4 dựa trên điểm tối đa (DiemMax)
 *
 * @param float $diemMax Điểm tối đa
 * @return array [DiemChu, DiemHeSo4]
 */
private function calculateGrade($diemMax)
{
    if ($diemMax >= 8.5) {
        return ['A', 4.0];
    } elseif ($diemMax >= 7.0) {
        return ['B', 3.0];
    } elseif ($diemMax >= 5.5) {
        return ['C', 2.0];
    } elseif ($diemMax >= 4.0) {
        return ['D', 1.0];
    } else {
        return ['F', 0.0];
    }
}

public function getStudentScores($studentId, $hocphanId)
{
    try {
        // Truy vấn điểm của sinh viên theo studentId và hocphanId
        $scores = DB::table('enroll_results')
            ->join('enrollments', 'enroll_results.enroll_id', '=', 'enrollments.id')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->select(
                'hoc_phans.title as hocphan_title',
                'hoc_phans.tinchi as so_tin_chi',
                'hoc_phans.is_condition_course', // Thêm trường is_condition_course
                'phancong.hocphan_id',
                'enroll_results.DiemBP',
                'enroll_results.Thi1',
                'enroll_results.Diem1',
                'enroll_results.Thi2',
                'enroll_results.Diem2',
                'enroll_results.DiemMax',
                'enroll_results.DiemChu',
                'enroll_results.DiemHeSo4'
            )
            ->where('enroll_results.student_id', $studentId)
            ->where('phancong.hocphan_id', $hocphanId) // Thêm điều kiện lọc theo hocphanId
            ->get();

        if ($scores->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có bản ghi điểm nào cho sinh viên này trong học phần này',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Danh sách điểm của sinh viên',
            'data' => $scores,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}

// /**
//      * API: Lấy thông tin tiến độ học tập của sinh viên
//      * GET /student-progress/{studentId}
//      */
//     public function getStudentProgress($studentId)
//     {
//         try {
//             // Bước 1: Lấy nganh_id của sinh viên từ bảng students
//             $student = DB::table('students')
//                 ->where('id', $studentId)
//                 ->select('nganh_id')
//                 ->first();

//             if (!$student) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Không tìm thấy sinh viên với ID này',
//                 ], 404);
//             }

//             $nganhId = $student->nganh_id;

//             // Bước 2: Lấy tổng số tín chỉ yêu cầu từ bảng chuong_trinh_dao_tao
//             $program = DB::table('chuong_trinh_dao_tao')
//                 ->where('nganh_id', $nganhId)
//                 ->where('status', 1) // Chỉ lấy chương trình đang hoạt động
//                 ->select('tong_tin_chi')
//                 ->first();

//             if (!$program) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => 'Không tìm thấy chương trình đào tạo cho ngành này',
//                 ], 404);
//             }

//             $requiredCredits = $program->tong_tin_chi;

//             // Bước 3: Lấy danh sách học phần đã đăng ký của sinh viên
//             $enrolledCourses = DB::table('enrollments')
//                 ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
//                 ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
//                 ->select(
//                     'hoc_phans.id as hocphan_id',
//                     'hoc_phans.title as hocphan_title',
//                     'hoc_phans.tinchi as so_tin_chi',
//                     'enrollments.id as enroll_id'
//                 )
//                 ->where('enrollments.student_id', $studentId)
//                 ->get();

//             if ($enrolledCourses->isEmpty()) {
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Sinh viên chưa đăng ký học phần nào',
//                     'data' => [
//                         'total_credits_completed' => 0,
//                         'total_credits' => 0,
//                         'gpa' => 0.0,
//                         'progress_percentage' => 0.0,
//                         'required_credits' => $requiredCredits,
//                         'courses' => [],
//                     ],
//                 ], 200);
//             }

//             // Bước 4: Lấy điểm của sinh viên và tính toán
//             $progressData = [];
//             $totalCreditsCompleted = 0;
//             $totalWeightedScore = 0;
//             $totalCredits = 0;

//             foreach ($enrolledCourses as $course) {
//                 $hocphanId = $course->hocphan_id;
//                 $enrollId = $course->enroll_id;

//                 // Lấy điểm của học phần
//                 $score = DB::table('enroll_results')
//                     ->where('enroll_id', $enrollId)
//                     ->where('student_id', $studentId)
//                     ->select('DiemMax', 'DiemChu', 'DiemHeSo4')
//                     ->first();

//                 $credits = $course->so_tin_chi;
//                 $diemHeSo4 = $score ? (float)$score->DiemHeSo4 : null;
//                 $diemChu = $score ? $score->DiemChu : null;

//                 // Kiểm tra học phần đã hoàn thành (có điểm và không rớt - DiemChu != "F")
//                 $isCompleted = $score && $diemChu && $diemChu !== 'F';

//                 if ($isCompleted) {
//                     $totalCreditsCompleted += $credits;
//                     if ($diemHeSo4 !== null) {
//                         $totalWeightedScore += $diemHeSo4 * $credits;
//                     }
//                 }
//                 $totalCredits += $credits;

//                 $progressData[] = [
//                     'hocphan_id' => $hocphanId,
//                     'title' => $course->hocphan_title,
//                     'so_tin_chi' => $credits,
//                     'diem_he_so_4' => $diemHeSo4,
//                     'diem_chu' => $diemChu,
//                     'is_completed' => $isCompleted,
//                 ];
//             }

//             // Bước 5: Tính GPA (điểm trung bình tích lũy)
//             $gpa = $totalCreditsCompleted > 0 ? $totalWeightedScore / $totalCreditsCompleted : 0.0;

//             // Bước 6: Tính tiến độ hoàn thành
//             $progressPercentage = $requiredCredits > 0 ? ($totalCreditsCompleted / $requiredCredits) * 100 : 0.0;

//             // Bước 7: Trả về kết quả
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Thông tin tiến độ học tập của sinh viên',
//                 'data' => [
//                     'total_credits_completed' => $totalCreditsCompleted,
//                     'total_credits' => $totalCredits,
//                     'gpa' => $gpa,
//                     'progress_percentage' => $progressPercentage,
//                     'required_credits' => $requiredCredits,
//                     'courses' => $progressData,
//                 ],
//             ], 200);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Lỗi: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     /**
//      * API: Lấy thông tin thống kê và báo cáo cho giảng viên
//      * GET /teacher-report/{teacherId}
//      */
//     public function getTeacherReport($teacherId)
//     {
//         try {
//             // Bước 1: Lấy danh sách học phần mà giảng viên phụ trách
//             $courses = DB::table('phancong')
//                 ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
//                 ->select(
//                     'phancong.id as phancong_id',
//                     'hoc_phans.id as hocphan_id',
//                     'hoc_phans.title as hocphan_title',
//                     'hoc_phans.tinchi as so_tin_chi'
//                 )
//                 ->where('phancong.giangvien_id', $teacherId) // Sửa teacher_id thành giangvien_id
//                 ->get();

//             if ($courses->isEmpty()) {
//                 return response()->json([
//                     'success' => true,
//                     'message' => 'Giảng viên chưa phụ trách học phần nào',
//                     'data' => [
//                         'total_courses' => 0,
//                         'total_students' => 0,
//                         'pass_rate' => 0.0,
//                         'average_score' => 0.0,
//                         'courses' => [],
//                     ],
//                 ], 200);
//             }

//             $totalStudents = 0;
//             $totalPassedStudents = 0;
//             $totalWeightedScore = 0;
//             $totalScoredStudents = 0;
//             $courseReports = [];

//             // Bước 2: Lấy thông tin sinh viên và điểm cho từng học phần
//             foreach ($courses as $course) {
//                 $phancongId = $course->phancong_id;
//                 $hocphanId = $course->hocphan_id;

//                 // Lấy danh sách sinh viên trong học phần
//                 $students = DB::table('enrollments')
//                     ->join('students', 'enrollments.student_id', '=', 'students.id')
//                     ->join('users', 'students.user_id', '=', 'users.id') // Join với bảng users
//                     ->join('classes', 'students.class_id', '=', 'classes.id')
//                     ->select(
//                         'students.id as student_id',
//                         'users.full_name as student_name', // Lấy full_name từ bảng users
//                         'classes.class_name as class_name'
//                     )
//                     ->where('enrollments.phancong_id', $phancongId)
//                     ->get();

//                 $courseStudents = [];
//                 $coursePassedStudents = 0;
//                 $courseWeightedScore = 0;
//                 $courseScoredStudents = 0;

//                 foreach ($students as $student) {
//                     $studentId = $student->student_id;

//                     // Lấy điểm của sinh viên
//                     $score = DB::table('enroll_results')
//                         ->where('student_id', $studentId)
//                         ->where('enroll_id', function ($query) use ($studentId, $phancongId) {
//                             $query->select('id')
//                                 ->from('enrollments')
//                                 ->where('student_id', $studentId)
//                                 ->where('phancong_id', $phancongId)
//                                 ->first();
//                         })
//                         ->select('DiemBP', 'Thi1', 'Thi2', 'DiemMax', 'DiemChu', 'DiemHeSo4')
//                         ->first();

//                     $diemHeSo4 = $score ? (float)$score->DiemHeSo4 : null;
//                     $diemChu = $score ? $score->DiemChu : null;

//                     // Kiểm tra sinh viên có đạt không
//                     $isPassed = $score && $diemChu && $diemChu !== 'F';

//                     if ($isPassed) {
//                         $coursePassedStudents++;
//                         $totalPassedStudents++;
//                     }
//                     if ($diemHeSo4 !== null) {
//                         $courseWeightedScore += $diemHeSo4;
//                         $courseScoredStudents++;
//                         $totalWeightedScore += $diemHeSo4;
//                         $totalScoredStudents++;
//                     }

//                     $courseStudents[] = [
//                         'student_id' => $studentId,
//                         'student_name' => $student->student_name, // Sử dụng student_name từ full_name
//                         'class_name' => $student->class_name,
//                         'diem_bp' => $score ? $score->DiemBP : null,
//                         'thi_1' => $score ? $score->Thi1 : null,
//                         'thi_2' => $score ? $score->Thi2 : null,
//                         'diem_max' => $score ? $score->DiemMax : null,
//                         'diem_chu' => $diemChu,
//                         'diem_he_so_4' => $diemHeSo4,
//                         'is_passed' => $isPassed,
//                     ];
//                 }

//                 $totalStudents += count($students);

//                 // Tính toán cho học phần
//                 $coursePassRate = count($students) > 0 ? ($coursePassedStudents / count($students)) * 100 : 0.0;
//                 $courseAverageScore = $courseScoredStudents > 0 ? $courseWeightedScore / $courseScoredStudents : 0.0;

//                 $courseReports[] = [
//                     'phancong_id' => $phancongId,
//                     'hocphan_id' => $hocphanId,
//                     'title' => $course->hocphan_title,
//                     'so_tin_chi' => $course->so_tin_chi,
//                     'total_students' => count($students),
//                     'passed_students' => $coursePassedStudents,
//                     'pass_rate' => $coursePassRate,
//                     'average_score' => $courseAverageScore,
//                     'students' => $courseStudents,
//                 ];
//             }

//             // Bước 3: Tính toán tổng quan
//             $totalPassRate = $totalStudents > 0 ? ($totalPassedStudents / $totalStudents) * 100 : 0.0;
//             $totalAverageScore = $totalScoredStudents > 0 ? $totalWeightedScore / $totalScoredStudents : 0.0;

//             // Bước 4: Trả về kết quả
//             return response()->json([
//                 'success' => true,
//                 'message' => 'Báo cáo thống kê cho giảng viên',
//                 'data' => [
//                     'total_courses' => count($courses),
//                     'total_students' => $totalStudents,
//                     'pass_rate' => $totalPassRate,
//                     'average_score' => $totalAverageScore,
//                     'courses' => $courseReports,
//                 ],
//             ], 200);
//         } catch (\Exception $e) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Lỗi: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

/**
 * API: Lấy thông tin tiến độ học tập của sinh viên
 * GET /student-progress/{studentId}
 */
public function getStudentProgress($studentId)
{
    try {
        // Bước 1: Lấy nganh_id của sinh viên từ bảng students
        $student = DB::table('students')
            ->where('id', $studentId)
            ->select('nganh_id')
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sinh viên với ID này',
            ], 404);
        }

        $nganhId = $student->nganh_id;

        // Bước 2: Lấy tổng số tín chỉ yêu cầu từ bảng chuong_trinh_dao_tao
        $program = DB::table('chuong_trinh_dao_tao')
            ->where('nganh_id', $nganhId)
            ->where('status', 1) // Chỉ lấy chương trình đang hoạt động
            ->select('tong_tin_chi')
            ->first();

        if (!$program) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chương trình đào tạo cho ngành này',
            ], 404);
        }

        $requiredCredits = $program->tong_tin_chi;

        // Bước 3: Lấy danh sách học phần đã đăng ký của sinh viên
        $enrolledCourses = DB::table('enrollments')
            ->join('phancong', 'enrollments.phancong_id', '=', 'phancong.id')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->select(
                'hoc_phans.id as hocphan_id',
                'hoc_phans.title as hocphan_title',
                'hoc_phans.tinchi as so_tin_chi',
                'hoc_phans.is_condition_course', // Thêm trường is_condition_course
                'enrollments.id as enroll_id'
            )
            ->where('enrollments.student_id', $studentId)
            ->get();

        if ($enrolledCourses->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Sinh viên chưa đăng ký học phần nào',
                'data' => [
                    'total_credits_completed' => 0,
                    'total_credits' => 0,
                    'gpa' => 0.0,
                    'progress_percentage' => 0.0,
                    'required_credits' => $requiredCredits,
                    'courses' => [],
                ],
            ], 200);
        }

        // Bước 4: Lấy điểm của sinh viên và tính toán
        $progressData = [];
        $totalCreditsCompleted = 0;
        $totalWeightedScore = 0;
        $totalCreditsForGPA = 0; // Tổng tín chỉ dùng để tính GPA (bỏ qua học phần điều kiện)
        $totalCredits = 0;

        foreach ($enrolledCourses as $course) {
            $hocphanId = $course->hocphan_id;
            $enrollId = $course->enroll_id;
            $isConditionCourse = $course->is_condition_course;

            // Lấy điểm của học phần
            $score = DB::table('enroll_results')
                ->where('enroll_id', $enrollId)
                ->where('student_id', $studentId)
                ->select('DiemMax', 'DiemChu', 'DiemHeSo4')
                ->first();

            $credits = $course->so_tin_chi;
            $diemHeSo4 = $score ? (float)$score->DiemHeSo4 : null;
            $diemChu = $score ? $score->DiemChu : null;

            // Kiểm tra học phần đã hoàn thành (có điểm và không rớt - DiemChu != "F")
            $isCompleted = $score && $diemChu && $diemChu !== 'F';

            if ($isCompleted) {
                $totalCreditsCompleted += $credits; // Tín chỉ hoàn thành vẫn tính cả học phần điều kiện
                if (!$isConditionCourse && $diemHeSo4 !== null) {
                    // Chỉ tính GPA cho học phần không phải điều kiện
                    $totalWeightedScore += $diemHeSo4 * $credits;
                    $totalCreditsForGPA += $credits;
                }
            }
            $totalCredits += $credits;

            $progressData[] = [
                'hocphan_id' => $hocphanId,
                'title' => $course->hocphan_title,
                'so_tin_chi' => $credits,
                'diem_he_so_4' => $diemHeSo4,
                'diem_chu' => $diemChu,
                'is_completed' => $isCompleted,
                'is_condition_course' => $isConditionCourse, // Trả về thông tin học phần điều kiện
            ];
        }

        // Bước 5: Tính GPA (điểm trung bình tích lũy)
        $gpa = $totalCreditsForGPA > 0 ? $totalWeightedScore / $totalCreditsForGPA : 0.0;

        // Bước 6: Tính tiến độ hoàn thành
        $progressPercentage = $requiredCredits > 0 ? ($totalCreditsCompleted / $requiredCredits) * 100 : 0.0;

        // Bước 7: Trả về kết quả
        return response()->json([
            'success' => true,
            'message' => 'Thông tin tiến độ học tập của sinh viên',
            'data' => [
                'total_credits_completed' => $totalCreditsCompleted,
                'total_credits' => $totalCredits,
                'gpa' => $gpa,
                'progress_percentage' => $progressPercentage,
                'required_credits' => $requiredCredits,
                'courses' => $progressData,
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
 * API: Lấy thông tin thống kê và báo cáo cho giảng viên
 * GET /teacher-report/{teacherId}
 */
public function getTeacherReport($teacherId)
{
    try {
        // Bước 1: Lấy danh sách học phần mà giảng viên phụ trách
        $courses = DB::table('phancong')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->select(
                'phancong.id as phancong_id',
                'hoc_phans.id as hocphan_id',
                'hoc_phans.title as hocphan_title',
                'hoc_phans.tinchi as so_tin_chi',
                'hoc_phans.is_condition_course' // Thêm trường is_condition_course
            )
            ->where('phancong.giangvien_id', $teacherId)
            ->get();

        if ($courses->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Giảng viên chưa phụ trách học phần nào',
                'data' => [
                    'total_courses' => 0,
                    'total_students' => 0,
                    'pass_rate' => 0.0,
                    'average_score' => 0.0,
                    'courses' => [],
                ],
            ], 200);
        }

        $totalStudents = 0;
        $totalPassedStudents = 0;
        $totalWeightedScore = 0;
        $totalScoredStudents = 0;
        $courseReports = [];

        // Bước 2: Lấy thông tin sinh viên và điểm cho từng học phần
        foreach ($courses as $course) {
            $phancongId = $course->phancong_id;
            $hocphanId = $course->hocphan_id;
            $isConditionCourse = $course->is_condition_course;

            // Lấy danh sách sinh viên trong học phần
            $students = DB::table('enrollments')
                ->join('students', 'enrollments.student_id', '=', 'students.id')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->join('classes', 'students.class_id', '=', 'classes.id')
                ->select(
                    'students.id as student_id',
                    'users.full_name as student_name',
                    'classes.class_name as class_name'
                )
                ->where('enrollments.phancong_id', $phancongId)
                ->get();

            $courseStudents = [];
            $coursePassedStudents = 0;
            $courseWeightedScore = 0;
            $courseScoredStudents = 0;

            foreach ($students as $student) {
                $studentId = $student->student_id;

                // Lấy điểm của sinh viên
                $score = DB::table('enroll_results')
                    ->where('student_id', $studentId)
                    ->where('enroll_id', function ($query) use ($studentId, $phancongId) {
                        $query->select('id')
                            ->from('enrollments')
                            ->where('student_id', $studentId)
                            ->where('phancong_id', $phancongId)
                            ->first();
                    })
                    ->select('DiemBP', 'Thi1', 'Thi2', 'DiemMax', 'DiemChu', 'DiemHeSo4')
                    ->first();

                $diemHeSo4 = $score ? (float)$score->DiemHeSo4 : null;
                $diemChu = $score ? $score->DiemChu : null;

                // Kiểm tra sinh viên có đạt không
                $isPassed = $score && $diemChu && $diemChu !== 'F';

                if ($isPassed) {
                    $coursePassedStudents++;
                    $totalPassedStudents++;
                }
                if (!$isConditionCourse && $diemHeSo4 !== null) {
                    // Chỉ tính điểm trung bình cho học phần không phải điều kiện
                    $courseWeightedScore += $diemHeSo4;
                    $courseScoredStudents++;
                    $totalWeightedScore += $diemHeSo4;
                    $totalScoredStudents++;
                }

                $courseStudents[] = [
                    'student_id' => $studentId,
                    'student_name' => $student->student_name,
                    'class_name' => $student->class_name,
                    'diem_bp' => $score ? $score->DiemBP : null,
                    'thi_1' => $score ? $score->Thi1 : null,
                    'thi_2' => $score ? $score->Thi2 : null,
                    'diem_max' => $score ? $score->DiemMax : null,
                    'diem_chu' => $diemChu,
                    'diem_he_so_4' => $diemHeSo4,
                    'is_passed' => $isPassed,
                    'is_condition_course' => $isConditionCourse, // Thêm thông tin học phần điều kiện
                ];
            }

            $totalStudents += count($students);

            // Tính toán cho học phần
            $coursePassRate = count($students) > 0 ? ($coursePassedStudents / count($students)) * 100 : 0.0;
            $courseAverageScore = $courseScoredStudents > 0 ? $courseWeightedScore / $courseScoredStudents : 0.0;

            $courseReports[] = [
                'phancong_id' => $phancongId,
                'hocphan_id' => $hocphanId,
                'title' => $course->hocphan_title,
                'so_tin_chi' => $course->so_tin_chi,
                'is_condition_course' => $isConditionCourse, // Thêm thông tin học phần điều kiện
                'total_students' => count($students),
                'passed_students' => $coursePassedStudents,
                'pass_rate' => $coursePassRate,
                'average_score' => $courseAverageScore,
                'students' => $courseStudents,
            ];
        }

        // Bước 3: Tính toán tổng quan
        $totalPassRate = $totalStudents > 0 ? ($totalPassedStudents / $totalStudents) * 100 : 0.0;
        $totalAverageScore = $totalScoredStudents > 0 ? $totalWeightedScore / $totalScoredStudents : 0.0;

        // Bước 4: Trả về kết quả
        return response()->json([
            'success' => true,
            'message' => 'Báo cáo thống kê cho giảng viên',
            'data' => [
                'total_courses' => count($courses),
                'total_students' => $totalStudents,
                'pass_rate' => $totalPassRate,
                'average_score' => $totalAverageScore,
                'courses' => $courseReports,
            ],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage(),
        ], 500);
    }
}
}

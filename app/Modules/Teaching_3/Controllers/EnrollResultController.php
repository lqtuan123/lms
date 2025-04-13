<?php

namespace App\Modules\Teaching_3\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Teaching_3\Models\EnrollResult;
use App\Modules\Teaching_3\Models\Enrollment;
use App\Modules\Teaching_1\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnrollResultController extends Controller
{
    protected $pagesize;

    public function __construct()
    {
        $this->pagesize = env('NUMBER_PER_PAGE', 20);
        $this->middleware('auth');
    }

    public function index()
    {
        $active_menu = "enroll_results_list";
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Danh sách kết quả khóa học</li>';

        $enrollResults = EnrollResult::with(['enrollment', 'student'])
            ->orderBy('id', 'DESC')
            ->paginate($this->pagesize);

        return view('Teaching_3::enroll_results.index', compact('enrollResults', 'breadcrumb', 'active_menu'));
    }

    public function create()
    {
        $active_menu = 'enroll_results_add';
        $enrollments = Enrollment::all();
        $students = Student::with('user')->get();
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thêm kết quả học tập</li>';

        return view('Teaching_3::enroll_results.create', compact('enrollments', 'students', 'breadcrumb', 'active_menu'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'enroll_id'  => 'required|exists:enrollments,id',
            'student_id' => 'required|exists:students,id',
            'diem30'     => 'nullable|numeric|min:0|max:30',
            'diem70'     => 'nullable|numeric|min:0|max:70',
        ]);

        EnrollResult::create($validatedData);

        return redirect()->route('admin.enroll_results.index')->with('success', 'Kết quả học tập được thêm thành công.');
    }

    public function edit($id)
    {
        $active_menu = 'enroll_result_edit';
        $enrollResult = EnrollResult::findOrFail($id);
        $enrollments = Enrollment::all();
        $students = Student::all();
        $breadcrumb = '
            <li class="breadcrumb-item"><a href="#">/</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa kết quả học tập</li>';

        return view('Teaching_3::enroll_results.edit', compact(
            'enrollResult', 'enrollments', 'students', 'breadcrumb', 'active_menu'
        ));
    }

    public function update(Request $request, $id)
    {
        try {
            $enrollResult = EnrollResult::findOrFail($id);

            $validatedData = $request->validate([
                'enroll_id'  => 'required|exists:enrollments,id',
                'student_id' => 'required|exists:students,id',
                'diem30'     => 'nullable|numeric|min:0|max:30',
                'diem70'     => 'nullable|numeric|min:0|max:70',
            ]);

            $enrollResult->update($validatedData);

            return redirect()->route('admin.enroll_results.index')->with('success', 'Cập nhật kết quả học tập thành công.');
        } catch (\Exception $e) {
            Log::error('Error updating enroll result:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Đã xảy ra lỗi khi cập nhật dữ liệu.');
        }
    }

    public function destroy($id)
    {
        $enrollResult = EnrollResult::findOrFail($id);
        $enrollResult->delete();

        return redirect()->route('admin.enroll_results.index')->with('success', 'Kết quả học tập đã được xóa.');
    }
}

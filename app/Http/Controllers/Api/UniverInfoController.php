<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UniverInfoController extends Controller
{
    //
    public function getNganhs(Request $request) 
    {
        try {
            $donviId = $request->input('donvi_id');
    
            $nganhs = \App\Modules\Teaching_1\Models\Nganh::where('donvi_id', $donviId)->get();
    
            return response()->json([
                'success' => true,
                'data' => $nganhs,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lấy danh sách ngành: ' . $e->getMessage(),
            ], 500);
        }
    }
    

public function getDonVis() 
{
    try {
        // Lấy tất cả dữ liệu từ bảng `donvi`
        $donVis = \App\Modules\Teaching_1\Models\Donvi::all();

        return response()->json([
            'success' => true,
            'data' => $donVis,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách đơn vị: ' . $e->getMessage(),
        ], 500);
    }
}

public function chuyenNganhs() 
{
    try {
        // Lấy tất cả dữ liệu từ bảng `chuyennganhs`
        $chuyenNganhs = \App\Modules\Teaching_1\Models\ChuyenNganh::all();

        return response()->json([
            'success' => true,
            'data' => $chuyenNganhs,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách chuyên ngành: ' . $e->getMessage(),
        ], 500);
    }
}

public function classes(Request $request) 
{
    try {
        $nganhId = $request->input('nganh_id');

        $classes = \App\Modules\Teaching_1\Models\ClassModel::where('nganh_id', $nganhId)->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách lớp: ' . $e->getMessage(),
        ], 500);
    }
}

public function getclasses(Request $request) 
{
    try {
        $teacherId = $request->input('teacher_id');

        $classes = \App\Modules\Teaching_1\Models\ClassModel::where('teacher_id', $teacherId)->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách lớp: ' . $e->getMessage(),
        ], 500);
    }
}

// Lấy danh sách phân công theo giangvien_id
public function phancong(Request $request)
{
    try {
        if (!$request->has('giangvien_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu tham số giangvien_id'
            ], 400);
        }

        // Join các bảng liên quan
        $phancongs = DB::table('phancong')
            ->join('hoc_phans', 'phancong.hocphan_id', '=', 'hoc_phans.id')
            ->join('teacher', 'phancong.giangvien_id', '=', 'teacher.id')
            ->join('classes', 'phancong.class_id', '=', 'classes.id')
            ->join('users', 'teacher.user_id', '=', 'users.id')
            ->select(
                'phancong.id as phancong_id',
                'hoc_phans.id as hocphan_id',
                'hoc_phans.title as hocphan_title',
                'hoc_phans.tinchi as tinchi',
                'hoc_phans.code as hocphan_code',
                'classes.class_name as class_course',  // Thay đổi tên cột class_course
                'phancong.ngayphancong',
                'users.full_name as teacher_name'
            )
            ->where('phancong.giangvien_id', $request->giangvien_id)
            ->orderBy('phancong.ngayphancong', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sách phân công theo giangvien_id',
            'data' => $phancongs
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi khi lấy danh sách phân công: ' . $e->getMessage()
        ], 500);
    }
}


}

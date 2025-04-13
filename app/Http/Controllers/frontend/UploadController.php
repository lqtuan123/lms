<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Lưu file vào thư mục storage/app/public/uploads/books
            $path = $file->storeAs('uploads/books', $fileName, 'public');
            
            // Trả về đường dẫn đầy đủ của file
            return response()->json([
                'success' => true,
                'link' => '/storage/' . $path
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy file upload'
        ], 400);
    }
} 
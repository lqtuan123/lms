<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Http\File;

class FilesFrontendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['avatarUpload', 'ckeditorUpload']]);
    }

    private function storeFile($file, $folder, $convertToWebP = false, $filename = null)
    {
        $awsKey = env('AWS_ACCESS_KEY_ID');
        $awsSecret = env('AWS_SECRET_ACCESS_KEY');
        $disk = ($awsKey && $awsSecret) ? 's3' : 'local';
        $folder = ($disk === 'local') ? "public/$folder" : $folder;

        $filename = $filename ?? Str::random(25);
        $extension = $convertToWebP ? 'webp' : $file->getClientOriginalExtension();
        $filePath = "$folder/$filename.$extension";

        if ($convertToWebP) {
            $tempPath = sys_get_temp_dir() . "/$filename.webp";

            $manager = new ImageManager(new Driver()); // Chạy với GD
            $image = $manager->read($file)->toWebp(90);
            $image->save($tempPath);

            Storage::disk($disk)->put($filePath, file_get_contents($tempPath));
            unlink($tempPath);
        } else {
            Storage::disk($disk)->putFileAs($folder, $file, "$filename.$extension");
        }

        $link = Storage::disk($disk)->url($filePath);
        return ($disk === 'local') ? asset($link) : $link;
    }

    public function uploadImage(Request $request, $folder, $convertToWebP = false)
    {
        try {
            $request->validate(['photo' => 'required|image|max:10240']);

            if (!$request->hasFile('photo')) {
                return response()->json(['status' => false, 'message' => 'Không có file được tải lên'], 400);
            }

            $file = $request->file('photo');
            
            if ($file->getSize() > 10485760) {
                return response()->json(['status' => false, 'message' => 'Kích thước tệp quá lớn, tối đa 10MB'], 400);
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                return response()->json(['status' => false, 'message' => 'Loại tệp không được hỗ trợ, chỉ chấp nhận JPG, PNG, GIF, WEBP'], 400);
            }
            
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $link = $this->storeFile($file, $folder, $convertToWebP, $filename);

            return response()->json(['status' => true, 'link' => $link]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('File upload error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status' => false, 
                'message' => 'Lỗi khi tải lên: ' . $e->getMessage(),
                'debug_info' => env('APP_DEBUG') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function adimgUpload(Request $request)
    {
        return $this->uploadImage($request, 'avatar');
    }
    public function galleryUpload(Request $request)
    {
        return $this->uploadImage($request, 'gallery');
    }
    public function brandUpload(Request $request)
    {
        return $this->uploadImage($request, 'brand');
    }
    public function avatarUpload(Request $request)
    {
        return $this->uploadImage($request, 'avatar');
    }
    public function bannerUpload(Request $request)
    {
        return $this->uploadImage($request, 'banner');
    }
    public function productUpload(Request $request)
    {
        return $this->uploadImage($request, 'products');
    }
    public function fileUpload(Request $request)
    {
        return $this->uploadImage($request, 'categories');
    }

    public function blogimageUpload($url, $title = '')
    {
        $imageData = file_get_contents($url);
        if (!$imageData) return '';

        $title = $title ? "$title" : uniqid();
        $tempPath = sys_get_temp_dir() . "/$title.webp";

        $manager = new ImageManager(new Driver());
        $image = $manager->read($imageData)->toWebp(90);
        $image->save($tempPath);

        $link = $this->storeFile(new \Illuminate\Http\File($tempPath), 'blog', true, $title);
        unlink($tempPath);

        return $link;
    }

    public function ckeditorUpload(Request $request)
    {
        return $this->uploadImage($request, 'ckeditor', true);
    }
}

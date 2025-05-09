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

    /**
     * Return the s3 storage disk.
     *
     * @param string $disk
     * @return \Illuminate\Filesystem\FilesystemAdapter
     */
    private function getDisk($disk)
    {
        return Storage::disk($disk);
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
        $url = '';

        try {
            // Thử lưu vào AWS trước
            if ($awsKey && $awsSecret) {
                if ($convertToWebP) {
                    $tempPath = sys_get_temp_dir() . "/$filename.webp";
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($file)->toWebp(90);
                    $image->save($tempPath);
                    
                    Storage::disk('s3')->put($filePath, file_get_contents($tempPath));
                    unlink($tempPath);
                } else {
                    Storage::disk('s3')->putFileAs($folder, $file, "$filename.$extension");
                }
                
                // Tạo URL cho AWS
                $s3Url = env('AWS_URL', 'https://s3.' . env('AWS_DEFAULT_REGION', 'us-east-1') . '.amazonaws.com');
                $bucket = env('AWS_BUCKET', '');
                $url = rtrim($s3Url, '/') . '/' . $bucket . '/' . $filePath;
                
                Log::info('File uploaded to AWS successfully', ['path' => $filePath, 'url' => $url]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to upload to AWS: ' . $e->getMessage());
            // Nếu lưu AWS thất bại, tiếp tục với local storage
            $disk = 'local';
        }

        // Nếu không có AWS hoặc lưu AWS thất bại, lưu vào local
        if ($disk === 'local') {
            if ($convertToWebP) {
                $tempPath = sys_get_temp_dir() . "/$filename.webp";
                $manager = new ImageManager(new Driver());
                $image = $manager->read($file)->toWebp(90);
                $image->save($tempPath);
                
                Storage::disk('local')->put($filePath, file_get_contents($tempPath));
                unlink($tempPath);
            } else {
                Storage::disk('local')->putFileAs($folder, $file, "$filename.$extension");
            }
            
            // Tạo URL cho local storage
            $relativePath = 'storage/' . str_replace('public/', '', $filePath);
            $url = rtrim(config('app.url'), '/') . '/' . $relativePath;
            
            Log::info('File uploaded to local storage', ['path' => $filePath, 'url' => $url]);
        }

        return $url;
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
        Log::info('Avatar upload request received', ['has_file' => $request->hasFile('photo')]);
        
        if($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Processing avatar upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ]);
                
                // Lưu vào storage
                $path = $file->storeAs('avatar', $filename, 'public');
                // Tạo URL đầy đủ với domain và đường dẫn storage
                $relativePath = 'storage/' . str_replace('public/', '', $path);
                $url = rtrim(config('app.url'), '/') . '/' . $relativePath;
                
                Log::info('Avatar uploaded successfully', ['path' => $path, 'url' => $url]);
                
                return response()->json([
                    'status' => true,
                    'url' => $url,
                    'link' => $url,
                    'message' => 'Tải lên ảnh đại diện thành công!'
                ]);
            } catch (\Exception $e) {
                Log::error('Avatar upload error: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi khi tải lên ảnh: ' . $e->getMessage()
                ], 500);
            }
        }
        
        Log::warning('Avatar upload failed: No file provided');
        return response()->json([
            'status' => false,
            'message' => 'Không có file nào được tải lên'
        ], 400);
    }
    public function bannerUpload(Request $request)
    {
        Log::info('Banner upload request received', ['has_file' => $request->hasFile('banner')]);
        
        if($request->hasFile('banner')) {
            try {
                $file = $request->file('banner');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                Log::info('Processing banner upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ]);
                
                // Lưu vào storage
                $path = $file->storeAs('banner', $filename, 'public');
                // Tạo URL đầy đủ với domain và đường dẫn storage
                $relativePath = 'storage/' . str_replace('public/', '', $path);
                $url = rtrim(config('app.url'), '/') . '/' . $relativePath;
                
                Log::info('Banner uploaded successfully', ['path' => $path, 'url' => $url]);
                
                return response()->json([
                    'status' => true,
                    'url' => $url,
                    'message' => 'Tải lên ảnh bìa thành công!'
                ]);
            } catch (\Exception $e) {
                Log::error('Banner upload error: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Lỗi khi tải lên ảnh: ' . $e->getMessage()
                ], 500);
            }
        }
        
        Log::warning('Banner upload failed: No file provided');
        return response()->json([
            'status' => false,
            'message' => 'Không có file nào được tải lên'
        ], 400);
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

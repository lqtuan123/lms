<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Intervention\Image\Facades\Image;

class FilesController extends Controller
{
    public function __construct( )
    {
        $this->middleware('auth');
    }
    public function adimgUpload(Request $request)
    {
        // Validate the request
        $request->validate([
            'file' => 'required|image|max:2048',
        ]);

        $filename = $request->file('file')->getClientOriginalName();
        $ext = '.'.$request->file('file')->getClientOriginalExtension();
       
        $filename =  str_replace(  $ext , '',$filename);
        $link = $request->hasFile('file') ? $this->store2($request->file('file'), 'avatar',$filename) : null;
       
        return response()->json(['status'=>'true','link'=>$link]);
       
    }
    public function blogimageUpload($url,$title='')
    {
        $ch = curl_init($url);

        // Set options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HEADER, 0); // Don't include headers in the output
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        
        // Execute cURL request
        $imageData = curl_exec($ch);
        
        // Check for errors
        if(curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } 
        else 
        {
            if(!(isset($title) && $title != ''))
            {
                $title = '';
            }
            $title.=uniqid();
            $tempImagePath = tempnam(sys_get_temp_dir(), 'image');
            $tempPath  = sys_get_temp_dir() . '/'.$title.'.webp';
            // $tempImagePath = tempnam(sys_get_temp_dir(), 'image');
            // Chuyển đổi file sang WebP
            Image::make($imageData)
                ->encode('webp', 90) // Chuyển sang WebP, chất lượng 90%
                ->save($tempPath);

            // Lưu file WebP lên S3
            $s3Path =$title.'.webp' ;
            $awsKey = env('AWS_ACCESS_KEY_ID');
            $awsSecret = env('AWS_SECRET_ACCESS_KEY');
            if ($awsKey && $awsSecret) {
                $disk = 's3';
                $folder = 'blog';
            } else {
                $disk = 'local';
                $folder = 'public/blog';
            }
             
            // Lưu file tạm lên disk đã chọn
            $link = Storage::disk($disk)->putFileAs(
                $folder,                     // Thư mục lưu file
                new \Illuminate\Http\File($tempPath), // Đọc file từ đường dẫn tạm
                $s3Path                    // Tên file
            );
            
            // Lấy URL file vừa lưu
            $link = Storage::disk($disk)->url($link);
            
            if ($disk === 'local') {
                $link = asset($link);
            }
            // Xóa file tạm để giải phóng bộ nhớ
            unlink($tempPath);
            return $link ;
        }
        return '';
    }

    private function compressImage($imagePath, $mimeType)
    {
        // Load the image based on the MIME type
        switch ($mimeType) {
            case 'image/jpeg':
                $image =  @imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image =  @imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image =  @imagecreatefromgif($imagePath);
                break;
            default:
                // Unsupported image format
                return;
        }
        // Compress the image and overwrite the original file
        imagejpeg($image, $imagePath, 70); // Adjust compression quality as needed
        // Free up memory
        imagedestroy($image);
    }

    //
    public function galleryUpload(Request $request)
    {
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'gallery') : null;
        $link = Storage::disk('s3')->url($link);
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function brandUpload(Request $request)
    {
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'brand') : null;
        $link = Storage::disk('s3')->url($link);
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function avartarUpload(Request $request)
    {
        $filename = $request->file('file')->getClientOriginalName();
        $ext = '.'.$request->file('file')->getClientOriginalExtension();
       
        $filename =  str_replace(  $ext , '',$filename);
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'avatar',$filename) : null;
       
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function bannerUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
        ]);
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'avatar') : null;
        $link = Storage::disk('s3')->url($link);
        return response()->json(['status'=>'true','link'=>$link]);
    }
   public function ckeditorUpload(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
        ]);
    
        if ($request->hasFile('upload')) {

            // $filename_ten = $request->file('upload')->getClientOriginalName();
            // $ext = '.'.$request->file('upload')->getClientOriginalExtension();
            // $filename =  str_replace(  $ext , '',$filename_ten);
            // $filename = $filename . '_' .Str::random(5) .   $ext;
            // Lấy thông tin file gốc
            $originalFile = $request->file('upload');
            $originalName = $originalFile->getClientOriginalName();
            $ext = '.' . $originalFile->getClientOriginalExtension();

            // Lấy tên file không có phần mở rộng
            $filename = str_replace($ext, '', $originalName);
            $tempPath  = sys_get_temp_dir() . '/my_custom_name.webp';
            // $tempImagePath = tempnam(sys_get_temp_dir(), 'image');
            // Chuyển đổi file sang WebP
            Image::make($originalFile)
                ->encode('webp', 90) // Chuyển sang WebP, chất lượng 90%
                ->save($tempPath);

            // Lưu file WebP lên S3
            $s3Path =  $filename . '.webp';
            $awsKey = env('AWS_ACCESS_KEY_ID');
            $awsSecret = env('AWS_SECRET_ACCESS_KEY');
            if ($awsKey && $awsSecret) {
                $disk = 's3';
                $folder = 'ckeditor';
            } else {
                $disk = 'local';
                $folder = 'public/ckeditor';
            }
             
            $filename = $s3Path ;

            // Lưu file tạm lên disk đã chọn
            $link = Storage::disk($disk)->putFileAs(
                $folder,                     // Thư mục lưu file
                new \Illuminate\Http\File($tempPath), // Đọc file từ đường dẫn tạm
                $filename                    // Tên file
            );
            
            // Lấy URL file vừa lưu
            $link = Storage::disk($disk)->url($link);
            
            if ($disk === 'local') {
                $link = asset($link);
            }
            
            // Xóa file tạm để giải phóng bộ nhớ
            unlink($tempPath);

            
            return response()->json(['fileName' => $filename, 'uploaded'=> 1, 'url' => $link]);
        }
        return response()->json($response);
       
    }

    public function productUpload(Request $request)
    {
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'products') : null;
        $link = Storage::disk('s3')->url($link);
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function FileUpload(Request $request)
    {
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'Categories') : null;
        $link = Storage::disk('s3')->url($link);
        return response()->json(['success'=>$link]);
    }
    public function store(UploadedFile $file, $folder = null, $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);
        return   $file->storeAs(
            $folder,
            $name . "." . $file->getClientOriginalExtension(),
            's3'
        );
        // $image = $request->file('file');
        // $imageName = time().'.'.$image->extension();
        // $image->move(public_path('images'),$imageName);
        
    }
    public function store2(UploadedFile $file, $folder = null, $filename = null)
    {
        $awsKey = env('AWS_ACCESS_KEY_ID');
        $awsSecret = env('AWS_SECRET_ACCESS_KEY');
        if ($awsKey && $awsSecret) {
            // Store the file on S3
            $disk = 's3';
        } else {
            // Store the file locally
            $disk = 'local';
            $folder = 'public/'.$folder;
        }
        $name = !is_null($filename) ? $filename.'_'.Str::random(5) : Str::random(25);
        $link =  $file->storeAs(
            $folder,
            $name . "." . $file->getClientOriginalExtension(),
            $disk
        );
        $link = Storage::disk( $disk)->url($link);
        if($disk == 'local')
        {
            $link = asset( $link);
        }
        return $link;
        
    }
  
}

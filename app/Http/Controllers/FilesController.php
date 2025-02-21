<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
class FilesController extends Controller
{
    //
    public function ckeditorUpload(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust the validation rules as needed
        ]);
    
        if ($request->hasFile('upload')) {

            $filename_ten = $request->file('upload')->getClientOriginalName();
            $ext = '.'.$request->file('upload')->getClientOriginalExtension();
            $filename =  str_replace(  $ext , '',$filename_ten);
            $filename = $filename . '_' .Str::random(5) .   $ext;
            $awsKey = env('AWS_ACCESS_KEY_ID');
            $awsSecret = env('AWS_SECRET_ACCESS_KEY');
            if ($awsKey && $awsSecret) {
                // Store the file on S3
                $disk = 's3';
                $folder='ckupload';
            } else {
                // Store the file locally
                $disk = 'local';
                $folder='public/ckupload';
            }
            $file = $request->file('upload');
            $url  = $file->storeAs(
                $folder,
                $filename,
                $disk
            );
            $url = Storage::disk($disk)->url($url);
            if($disk == 'local')
            {
                $url = asset( $url);
            }
            return response()->json(['fileName' => $filename_ten, 'uploaded'=> 1, 'url' => $url]);
        }
        return response()->json($response);
    }

    public function avartarUpload(Request $request)
    {
        $filename = $request->file('file')->getClientOriginalName();
        $ext = '.'.$request->file('file')->getClientOriginalExtension();
       
        $filename =  str_replace(  $ext , '',$filename);
        // echo $filename;
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'avatar',$filename) : null;
       
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function productUpload(Request $request)
    {
        
        $filename = $request->file('file')->getClientOriginalName();
        $ext = '.'.$request->file('file')->getClientOriginalExtension();
        $filename =  str_replace(  $ext , '',$filename);
       
        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'products', $filename) : null;
        
        return response()->json(['status'=>'true','link'=>$link]);
    }
    public function blogimageUpload($url)
    {
        $imageContent = file_get_contents($url);
        // Save the image content to a temporary file
        $tempImagePath = tempnam(sys_get_temp_dir(), 'image');
        file_put_contents($tempImagePath, $imageContent);
        // Check if the file is an image
        $imageInfo = @getimagesize($tempImagePath);
        if (!$imageInfo) {
            // Delete the temporary file and return false
            unlink($tempImagePath);
            return false;
        }
        // Check if the file size exceeds 0.5 MB
        $fileSize = filesize($tempImagePath);
        if ($fileSize > 0.5 * 1024 * 1024) { // Convert MB to bytes
            // Compress the image
            $this->compressImage($tempImagePath, $imageInfo['mime']);
        }
        $s3Path = "blogs";
        $awsKey = env('AWS_ACCESS_KEY_ID');
        $awsSecret = env('AWS_SECRET_ACCESS_KEY');
        if ($awsKey && $awsSecret) {
            // Store the file on S3
            $disk = 's3';
            $folder='blogs';
        } else {
            // Store the file locally
            $disk = 'local';
            $folder='public/ckupload';
        }
        
        // Upload the temporary file to S3
        $s3Path = Storage::disk( $disk)->putFile($folder, new File($tempImagePath) );
        $s3Path = Storage::disk( $disk)->url( $s3Path);
        if($disk == 'local')
        {
            $s3Path = asset( $s3Path);
        }
        // Delete the temporary file
        unlink($tempImagePath);
        return $s3Path;
    }

    private function compressImage($imagePath, $mimeType)
    {
        // Load the image based on the MIME type
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($imagePath);
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

    public function FileUpload(Request $request)
    {
        $filename = $request->file('file')->getClientOriginalName();
        $ext = '.'.$request->file('file')->getClientOriginalExtension();
        $filename =  str_replace(  $ext , '',$filename);

        $link = $request->hasFile('file') ? $this->store($request->file('file'), 'Categories',$filename) : null;
        
        return response()->json(['success'=>$link]);
    }
    public function store(UploadedFile $file, $folder = null, $filename = null)
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

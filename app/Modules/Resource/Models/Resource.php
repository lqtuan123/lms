<?php

namespace App\Modules\Resource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\FilesController;
use Illuminate\Support\Facades\Storage;
use App\Modules\Resource\Models\FileDownload;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class Resource extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'file_name',
        'file_type',
        'file_size',
        'url',
        'type_code',
        'link_code',
        'code'
    ];
    public static function createDownloadLink($resource)
    {
        // Tạo token duy nhất
            
        $token = Str::random(32);

        if($resource->link_code == 'youtube')
            return null;
        // Lưu thông tin vào cơ sở dữ liệu
        $filedownload = FileDownload::where('file_path',$resource->url)->where('expires_at' ,'>',NOW())->first();
        if(!$filedownload)
        {
            $filedownload = FileDownload::create([
                'file_path' => $resource->url,
                'download_token' => $token,
                'is_downloaded' => false,
                'expires_at' => now()->addDay(), // Link hết hạn sau 1 ngày
            ]);
        }
        $token = $filedownload->download_token;
        
        // Trả link tải
        return route('download.file', ['token' => $token]);
    }

    //create
    public static function createUrlResource($title,$url,$type_code,$code='tblog')
    {
        $data = [
            'title' => $title,
            'code' => $code,
            'slug' => self::generateSlug($title),
        ];
        $data['type_code'] =$type_code;
        $data['link_code'] = 'file';
       
        $response = Http::get($url);

        if ($response->ok()) {
            $fileName = 'unknown_file';
            $contentDisposition = $response->header('Content-Disposition');
            $mimeType = $response->header('Content-Type');
            $data['file_type'] =  $mimeType ;
            // Lấy tên file từ Content-Disposition
            if ($contentDisposition) {
                preg_match('/filename="(.+)"/', $contentDisposition, $matches);
                $fileName = $matches[1] ?? 'unknown_file';
            }
        
            // Nếu không có tên file, suy đoán từ Content-Type
            if ($fileName === 'unknown_file' && $mimeType) {
                switch ($mimeType) {
                    case 'application/pdf':
                        $fileName = 'file.pdf';
                        break;
                    case 'audio/mpeg':
                        $fileName = 'file.mp3';
                        break;
                    case 'image/jpeg':
                        $fileName = 'file.jpg';
                        break;
                    default:
                        $fileName = 'file.unknown';
                }
            }
            $data['file_name'] = $fileName;
        }
        if(isset( $data['file_name']))
            $data['title'] = $data['file_name'];
        $data['url'] = $url;
      
        return self::create($data);
    }

    public static function createResource($request, $file = null, $moduleName = null)
    {
        $title = $request->title ?? 'Resource Default Title';
        $data = [
            'title' => $title,
            'code' => $moduleName,
            'slug' => self::generateSlug($title),
        ];

        if (isset($request->type_code)) {
            $data['type_code'] = $request->type_code;
        } else {
            $resourceType = self::determineResourceType($file);
            $data['type_code'] = $resourceType;
        }

        if (isset($request->link_code)) {
            $data['link_code'] = $request->link_code;
        } else {
            $linkTypes = self::generateLinkCode($file);
            $data['link_code'] = $linkTypes;
        }

        if ($file) {
            $filesController = new FilesController();
            $folder = 'uploads/resources';
            $url = $filesController->store($file, $folder);

            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
            $data['url'] = $url;
        }

        if (isset($request->url) && !$file) {
            $youtubeID = self::getYouTubeID($request->url);
            if ($youtubeID) {
                $data['url'] = "https://www.youtube.com/watch?v=" . $youtubeID;
            } else {
                $data['url'] = $request->url;
            }
        }

        return self::create($data);
    }

    //Update
    public function updateResource($request, $file = null)
    {
        // Thiết lập dữ liệu cần cập nhật
        $title = $request['title'] ?? $this->title;
        $data = [
            'title' => $title,
            'slug' => self::generateSlug($title, $this),
        ];

        // Kiểm tra và cập nhật type_code nếu có trong request
        if (isset($request['type_code'])) {
            $data['type_code'] = $request['type_code'];
        }

        // Kiểm tra và cập nhật link_code nếu có trong request
        if (isset($request['link_code'])) {
            $data['link_code'] = $request['link_code'];
        }

        // Nếu có file mới, xử lý file
        if ($file) {
            // Xóa file cũ nếu có
            $this->deleteFile();

            // Lưu file mới
            $filesController = new FilesController();
            $folder = 'uploads/resources';
            $url = $filesController->store($file, $folder);

            // Cập nhật dữ liệu file vào resource
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
            $data['url'] = $url;
        }

        // Nếu có URL mới (YouTube hoặc link khác), xử lý URL
        if (isset($request['url'])) {
            $youtubeID = self::getYouTubeID($request['url']);
            if ($youtubeID) {
                $data['url'] = "https://www.youtube.com/watch?v=" . $youtubeID;
            } else {
                $data['url'] = $request['url'];
            }
        }

        // Cập nhật dữ liệu trong resource
        $this->fill($data);
        $this->save();

        return $this;
    }


    //Xóa resource và file của nó.
    public function deleteResource()
    {
        $this->deleteFile();
        return $this->delete();
    }

    //xóa file liên kết với resource
    private function deleteFile()
    {
        if (empty($this->path)) {
            Log::warning("Path is empty, no file to delete.");
            return;
        }

        Log::info("Attempting to delete file at path: {$this->path}");

        $this->deleteFromDisk('public');
        $this->deleteFromDisk('s3');
    }

    //Xóa file từ disk
    private function deleteFromDisk($disk)
    {
        if (Storage::disk($disk)->exists($this->path)) {
            Storage::disk($disk)->delete($this->path);
            Log::info("File deleted from {$disk} disk: {$this->path}");
        }
    }

    // Tạo slug
    public static function generateSlug($title)
    {
        $slug = Str::slug($title);
        $count = self::where('slug', $slug)->count();

        if($count > 0)
        {
            $slug .= uniqid();
        }

        return $slug;
    }

    // Lấy ID YouTube từ URL.
    private static function getYouTubeID($url)
    {
        $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|.+\?v=)|youtu\.be\/)([^&\n?#]+)/';
        preg_match($pattern, $url, $matches);
        return $matches[1] ?? null;
    }

    // Xác định loại resource từ mimeType của file
    private static function determineResourceType($file)
    {
        if ($file instanceof \Illuminate\Http\UploadedFile) {
            $mimeType = $file->getMimeType();
            if (str_starts_with($mimeType, 'image/')) return 'Image';
            if (str_starts_with($mimeType, 'video/')) return 'Video';
            if (str_starts_with($mimeType, 'audio/')) return 'Audio';
            return 'Document';
        }
        return 'Document';
    }
    //   Sinh mã link_code từ URL.
    private static function generateLinkCode($url)
    {
        $linkType = ResourceLinkType::where('viewcode', 'LIKE', "%$url%")->first();

        if ($linkType) {
            return $linkType->code;
        }
        return null;
    }
}

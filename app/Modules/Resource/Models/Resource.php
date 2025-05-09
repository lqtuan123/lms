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
        $data['type_code'] = $type_code;
        
        // Kiểm tra nếu là URL YouTube, xử lý đặc biệt
        $youtubeID = self::getYouTubeID($url);
        if ($youtubeID) {
            $data['link_code'] = 'url';
            $data['title'] = "YouTube: " . ($title != uniqid() ? $title : 'Video');
            $data['url'] = $url;
            return self::create($data);
        }
        
        // Kiểm tra xem đây có phải URL thông thường hay là tệp tin
        $isFileUrl = false;
        try {
            $response = Http::get($url);
            
            if ($response->ok()) {
                $contentType = $response->header('Content-Type');
                $contentDisposition = $response->header('Content-Disposition');
                
                // Nếu có Content-Disposition hoặc là các loại file phổ biến thì đánh dấu là file
                if ($contentDisposition || 
                    strpos($contentType, 'application/') === 0 || 
                    strpos($contentType, 'audio/') === 0 || 
                    strpos($contentType, 'video/') === 0 || 
                    strpos($contentType, 'image/') === 0 || 
                    strpos($contentType, 'text/') === 0) {
                    $isFileUrl = true;
                }
                
                // Kiểm tra thêm phần mở rộng của URL
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
                if (in_array(strtolower($extension), $documentExtensions)) {
                    $isFileUrl = true;
                }
            }
        } catch (\Exception $e) {
            // Nếu không lấy được phản hồi, coi như URL thông thường
            \Illuminate\Support\Facades\Log::warning("Không thể kiểm tra URL: " . $e->getMessage());
        }
        
        // Đặt link_code dựa trên kiểu tài nguyên
        // Nếu là URL file, đánh dấu là 'file'
        if ($isFileUrl) {
            $data['link_code'] = 'file';
        } else {
            // Nếu là URL thông thường, đánh dấu là 'url'
            $data['link_code'] = 'url';
        }
       
        // Xử lý thông tin file nếu đây là URL của file
        if ($isFileUrl && isset($response) && $response->ok()) {
            $fileName = 'unknown_file';
            $mimeType = $response->header('Content-Type');
            $data['file_type'] = $mimeType;
            
            // Lấy tên file từ Content-Disposition
            if (isset($contentDisposition) && $contentDisposition) {
                preg_match('/filename="(.+)"/', $contentDisposition, $matches);
                $fileName = $matches[1] ?? 'unknown_file';
            }
        
            // Nếu không có tên file, suy đoán từ Content-Type hoặc từ URL
            if ($fileName === 'unknown_file') {
                // Suy đoán từ phần mở rộng của URL
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                if ($extension) {
                    $fileName = basename(parse_url($url, PHP_URL_PATH));
                } else if (isset($mimeType)) {
                    // Suy đoán từ Content-Type
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
                        case 'application/msword':
                            $fileName = 'file.doc';
                            break;
                        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                            $fileName = 'file.docx';
                            break;
                        default:
                            $fileName = 'file.' . $extension ?: 'unknown';
                    }
                }
            }
            
            // Chỉ thiết lập file_name nếu có tên file hợp lệ
            if ($fileName !== 'unknown_file' && $fileName !== 'file.unknown') {
                $data['file_name'] = $fileName;
            }
        }
        
        // Thiết lập tiêu đề và URL
        if (isset($data['file_name']) && $data['file_name'] !== 'unknown_file' && $data['file_name'] !== 'file.unknown') {
            $data['title'] = $data['file_name'];
        } else {
            // Nếu là URL thông thường, sử dụng URL làm tiêu đề mô tả
            $data['title'] = "Liên kết: " . ($title != uniqid() ? $title : parse_url($url, PHP_URL_HOST));
        }
        
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

        // Đảm bảo file luôn được đánh dấu là file khi nó là file tải lên
        if ($file) {
            $data['link_code'] = 'file';
        } else if (isset($request->link_code)) {
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
    public static function getYouTubeID($url)
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

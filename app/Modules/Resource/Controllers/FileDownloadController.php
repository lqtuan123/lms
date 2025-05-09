<?php
namespace App\Modules\Resource\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Resource\Models\Resource;
use App\Modules\Resource\Models\ResourceLinkType;
use App\Modules\Resource\Models\ResourceType;
use App\Modules\Resource\Models\FileDownload;
 
USE App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
class FileDownloadController extends Controller
{
    public function maildownload(Request $request )
    {
        $slug = $request->slug;
        $user = auth()->guard('web')->user();
        if(!$user)
            return redirect()->back()->with('error','bạn cần phải đăng nhập để nhận email!');
        $resource = Resource::where('slug',$slug)->first();
        $link = Resource::createDownloadLink($resource);
        Mail::to($user->email)->send(new \App\Mail\FileDownloadMail($link));
        return redirect()->back()->with('success','Đã gửi thông tin hướng dẫn vào email cho bạn!'  );
    }
    public static function downloadform($res_ids)
    {
        $user = auth()->guard('web')->user();
        
        if($user)
        {
            $resources = array();
            foreach($res_ids as $res_id)
            {
                $resources [] = Resource::find($res_id);
            }
            return view('Resource::partials.downloadlink',compact('resources'))->render();
        }
        else
        {
            return "<span><i class='feather icon-feather-download-cloud icon-extra-small  ' style='background:white'></i> Bạn cần <a href='".route('front.login')."'>đăng nhập</a> để có thể tải tài nguyên! </span> ";
        }
        
    }
    public function download(Request $request, $token)
    {
        // Tìm token trong cơ sở dữ liệu
        $download = FileDownload::where('download_token', $token)->first();

        // Kiểm tra nếu token không hợp lệ hoặc đã hết hạn
        if (!$download  || $download->expires_at < now()) {
            return response()->json(['message' => 'Liên kết không hợp lệ hoặc đã hết hạn.'], 403);
        }

        // Đánh dấu là đã tải
        $download->is_downloaded = true;
        $download->save();

        // Tìm resource liên quan nếu có
        $filePath = $download->file_path; // Đường dẫn file trên server
        $resource = Resource::find($download->resource_id);
        
        if (!$resource) {
            // Tìm bằng URL nếu không có resource_id
            $resource = Resource::where('url', $filePath)->first();
        }
        
        // Kiểm tra nếu file_name là file.unknown hoặc unknown_file thì ngăn không cho tải xuống
        if ($resource && ($resource->file_name == 'file.unknown' || $resource->file_name == 'unknown_file')) {
            return response()->json(['message' => 'Không thể tải xuống file không xác định.'], 403);
        }
        
        // Xử lý tải xuống file
        try {
            \Illuminate\Support\Facades\Log::info("Đang xử lý tải xuống: " . $filePath);
            
            // Tên file và loại file
            $fileName = $resource->file_name ?? 'download';
            $mimeType = $resource->file_type ?? 'application/octet-stream';
            
            // Ưu tiên xử lý như file cục bộ
            
            // Phương pháp 1: Kiểm tra trong thư mục storage public
            if (Storage::disk('public')->exists($filePath)) {
                \Illuminate\Support\Facades\Log::info("Tải xuống từ public storage: " . $filePath);
                $fullPath = Storage::disk('public')->path($filePath);
                return response()->download($fullPath, $fileName, ['Content-Type' => $mimeType]);
            }
            
            // Phương pháp 2: Kiểm tra bằng đường dẫn trong storage
            try {
                $storagePath = Storage::path($filePath);
                if (file_exists($storagePath)) {
                    \Illuminate\Support\Facades\Log::info("Tải xuống từ storage path: " . $storagePath);
                    return response()->download($storagePath, $fileName, ['Content-Type' => $mimeType]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Lỗi khi sử dụng Storage::path: " . $e->getMessage());
            }
            
            // Phương pháp 3: Kiểm tra file trong thư mục public
            $publicPath = public_path(ltrim($filePath, '/'));
            if (file_exists($publicPath)) {
                \Illuminate\Support\Facades\Log::info("Tải xuống từ public path: " . $publicPath);
                return response()->download($publicPath, $fileName, ['Content-Type' => $mimeType]);
            }
            
            // Phương pháp 3.1: Kiểm tra trong storage/uploads/resources
            $resourcePath = public_path('storage/uploads/resources/' . basename($filePath));
            if (file_exists($resourcePath)) {
                \Illuminate\Support\Facades\Log::info("Tải xuống từ resource path: " . $resourcePath);
                return response()->download($resourcePath, $fileName, ['Content-Type' => $mimeType]);
            }
            
            // Phương pháp 4: Thử xử lý như đường dẫn tương đối
            $basePath = base_path(ltrim($filePath, '/'));
            if (file_exists($basePath)) {
                \Illuminate\Support\Facades\Log::info("Tải xuống từ base path: " . $basePath);
                return response()->download($basePath, $fileName, ['Content-Type' => $mimeType]);
            }
            
            // Phương pháp 5: Thử xử lý nếu đường dẫn bắt đầu với /storage
            if (strpos($filePath, '/storage/') === 0) {
                $storagePublicPath = public_path(substr($filePath, 1)); // Bỏ dấu / đầu tiên
                if (file_exists($storagePublicPath)) {
                    \Illuminate\Support\Facades\Log::info("Tải xuống từ storage public path: " . $storagePublicPath);
                    return response()->download($storagePublicPath, $fileName, ['Content-Type' => $mimeType]);
                }
            }
            
            // Phương pháp 6: Thử xử lý nếu đường dẫn là URL
            if (filter_var($filePath, FILTER_VALIDATE_URL)) {
                \Illuminate\Support\Facades\Log::info("Tải xuống từ URL: " . $filePath);
                
                // Thử tải xuống từ URL
                try {
                    // Mã hóa URL đúng cách
                    $parsedUrl = parse_url($filePath);
                    if (isset($parsedUrl['path'])) {
                        $pathParts = explode('/', $parsedUrl['path']);
                        $encodedParts = array_map(function($part) {
                            return urlencode($part);
                        }, $pathParts);
                        $parsedUrl['path'] = implode('/', $encodedParts);
                    }
                    
                    // Tái tạo URL từ các thành phần đã mã hóa
                    $encodedUrl = $this->buildUrl($parsedUrl);
                    
                    $response = Http::get($encodedUrl);
                    
                    if ($response->ok()) {
                        return response($response->body())
                            ->header('Content-Type', $mimeType)
                            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
                    } else {
                        return response()->json(['error' => 'Không thể tải file từ URL. Trạng thái: ' . $response->status()], 404);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Lỗi khi tải từ URL: " . $e->getMessage());
                    return response()->json(['error' => 'Lỗi khi tải xuống từ URL: ' . $e->getMessage()], 500);
                }
            }
            
            // Không tìm thấy file ở bất kỳ vị trí nào
            \Illuminate\Support\Facades\Log::error("Không tìm thấy file: " . $filePath);
            return response()->json(['error' => 'Không thể tìm thấy file tại đường dẫn: ' . $filePath], 404);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi tải xuống file: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi tải xuống file: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Tái tạo URL từ các thành phần đã được phân tích
     * 
     * @param array $parsedUrl Các thành phần URL từ parse_url()
     * @return string URL đã được tái tạo
     */
    private function buildUrl($parsedUrl) {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
        
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}
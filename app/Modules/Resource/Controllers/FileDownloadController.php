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
        $user = auth()->user();
        if(!$user)
            return redirect()->back()->with('error','bạn cần phải đăng nhập để nhận email!');
        $resource = Resource::where('slug',$slug)->first();
        $link = Resource::createDownloadLink($resource);
        Mail::to($user->email)->send(new \App\Mail\FileDownloadMail($link));
        return redirect()->back()->with('success','Đã gửi thông tin hướng dẫn vào email cho bạn!'  );
    }
    public static function downloadform($res_ids)
    {
        $user = auth()->user();
        
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

        // Trả về file cho người dùng
        $filePath = $download->file_path; // Đường dẫn file trên server
        $response = Http::get($filePath);

        if ($response->ok()) {
            $fileName = 'unknown_file';
            $contentDisposition = $response->header('Content-Disposition');
            $mimeType = $response->header('Content-Type');
        
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
        
            return response($response->body())
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
        }
        
        return response()->json(['error' => 'Không thể tải file từ S3'], 404);
    }
}
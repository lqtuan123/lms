<?php

namespace App\Modules\Resource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\FilesController;
use Illuminate\Support\Facades\Storage;

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

    //create
    public static function createResource($request, $file = null, $moduleName = null)
    {
        $title = $request->title ?? 'Resource Default Title';
        $description = $request->description ?? 'description';
        $data = [
            'title' => $title,
            'code' => $moduleName,
            'slug' => self::generateSlug($title),
            'description' => $description
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
        $title = $request['title'] ?? $this->title;
        $data = [
            'title' => $title,
            'slug' => self::generateSlug($title, $this),
        ];

        if (isset($request['type_code'])) {
            $data['type_code'] = $request['type_code'];
        }

        if (isset($request['link_code'])) {
            $data['link_code'] = $request['link_code'];
        }

        if ($file) {
            $this->deleteResource();

            $filesController = new FilesController();
            $folder = 'uploads/resources';
            $url = $filesController->store($file, $folder);

            $data['file_name'] = $file->getClientOriginalName();
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
            $data['url'] = $url;
        }

        if (isset($request['url'])) {
            $youtubeID = self::getYouTubeID($request['url']);
            if ($youtubeID) {
                $data['url'] = "https://www.youtube.com/watch?v=" . $youtubeID;
            } else {
                $data['url'] = $request['url'];
            }
        }

        $this->fill($data);
        $this->save();

        return $this;
    }

    public function deleteResource()
    {
        $this->deleteFile();
        return $this->delete();
    }

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
        $timestamp = now()->format('YmdHis'); 
        $slug = $slug . '-' . $timestamp;

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

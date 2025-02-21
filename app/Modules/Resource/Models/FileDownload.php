<?php

namespace App\Modules\Resource\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Controllers\FilesController;
use Illuminate\Support\Facades\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Mail;

class FileDownload extends Model
{
   
    protected $fillable = [
        'file_path',
        'download_token',
        'is_downloaded',
        'expires_at',
        
    ];
    
}

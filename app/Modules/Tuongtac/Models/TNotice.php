<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TNotice extends Model
{
    use HasFactory;
    protected $table = 't_notices'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'item_id',
        'item_code',
        'title', 
        'url_view',
        'user_id',
        'seen',
        
    ];
    
    
}
 
 
<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TMotion extends Model
{
    use HasFactory;
    protected $table = 't_motions'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'icon',
        'status', 
        
    ];
   
}
 
 
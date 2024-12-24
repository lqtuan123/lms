<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TUserpage extends Model
{
    use HasFactory;
    protected $table = 't_userpage_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'user_id',
        'page_id',
        'item_id',
        'item_code',
        'location',
        'status',
    ];
  
    
    
}
 
 
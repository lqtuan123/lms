<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TItem extends Model
{
    use HasFactory;
    protected $table = 't_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'item_code',
        'urlview', 
        
    ];
    
    
}
 
 
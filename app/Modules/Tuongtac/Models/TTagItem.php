<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TTagItem extends Model
{
    use HasFactory;
    protected $table = 't_tag_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'tag_id',
        'item_id',
        'item_code', 
        'hit',
        
    ];
    
    
}
 
 
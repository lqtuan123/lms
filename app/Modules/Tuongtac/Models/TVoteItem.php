<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TVoteItem extends Model
{
    use HasFactory;
    protected $table = 't_vote_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'item_id',
        'item_code',
        'count',
        'point',
        'votes',
         
    ];
    protected $casts = [
        'votes' => 'array', // Chuyển đổi thành mảng tự động
    ];
    
}
 
 
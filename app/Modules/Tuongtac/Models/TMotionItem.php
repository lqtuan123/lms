<?php

namespace App\Modules\Tuongtac\Models;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TMotionItem extends Model
{
    use HasFactory;
    protected $table = 't_motion_items'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'item_id',
        'item_code',
        'motions', 
        'user_motions',
    ];
    
    protected $casts = [
        'motions' => 'array', // Chuyển đổi thành mảng tự động
        'user_motions'=> 'array',
    ];
   
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'item_id')->where('item_code', 'tblog');
    }
}
 
 
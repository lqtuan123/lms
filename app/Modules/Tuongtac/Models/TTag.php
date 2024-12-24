<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TTag extends Model
{
    use HasFactory;
    protected $table = 't_tags'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'slug',
        'hit',
    ];
    
    public function blogs()
    {
        return $this->belongsToMany(TBlog::class, 't_tag_items',  'tag_id','item_id')
        ->withPivot('item_code') 
        ->wherePivot('item_code', 'tblog'); // Điều kiện trên bảng trung gian
    }
}
 
 
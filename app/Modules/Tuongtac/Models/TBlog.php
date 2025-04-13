<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TBlog extends Model
{
    use HasFactory;
    protected $table = 't_blogs'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'slug',
        'content', 
        'photo',
        'user_id',
        'hit',
        'status',
        'resources',
        'group_id',
    ];
    protected $casts = [
        'resources' => 'array', // Chuyển đổi thành mảng tự động
    ];
    public function tags()
    {
        return $this->belongsToMany(TTag::class, 't_tag_items', 'item_id','tag_id')
        ->withPivot('item_code') 
        ->wherePivot('item_code', 'tblog'); // Điều kiện trên bảng trung gian;
     
    }

       // return TTag::where('item_id',$this->id)->where('item_code','blog')->get();
    // public function motion()
    // {
    //     return $this->hasOne(TMotionItem::class, 'item_id')
    //                 ->where('item_code', 'tblog'); // Điều kiện `item_code = blog`
    // }
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id');
    }
    
    public function group()
    {
        return $this->belongsTo(\App\Modules\Group\Models\Group::class, 'group_id');
    }

    // protected $casts = [
    //     'photo' => 'array', // Chuyển đổi thành mảng tự động
    // ];
}
 
 
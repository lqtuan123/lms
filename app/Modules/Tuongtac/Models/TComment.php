<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TComment extends Model
{
    use HasFactory;
    protected $table = 't_comments'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'item_id',
        'item_code',
        'user_id', 
        'parent_id',
        'content', 
        'resources',
        'status'
    ];
     
    protected $casts = [
        'resources' => 'array', // Chuyển đổi thành mảng tự động
    ];
    public function statusChange()
    {
       
        if($status =='active')
        {
            $status = "inactive";
        }
        else
            $status = "active";
        $this->save();
    }
}
 
 
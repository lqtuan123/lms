<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
        'user_from_id',
        'seen',
    ];
    
    /**
     * Người gửi thông báo
     */
    public function userFrom()
    {
        return $this->belongsTo(User::class, 'user_from_id');
    }
    
    /**
     * Người nhận thông báo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
 
 
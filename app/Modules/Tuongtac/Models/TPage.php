<?php

namespace App\Modules\Tuongtac\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TPage extends Model
{
    use HasFactory;
    protected $table = 't_pages'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'slug',
        'description', 
        'banner',
        'avatar',
        'item_id',
        'item_code',
        'status',
    ];
    
    public static function getPageUrl($userId, $type = 'user')
    {
        if ($type == 'user') {
            return route('front.user.profile', ['id' => $userId]);
        } else {
            $userpage = self::where('user_id', $userId)->first();
            if ($userpage) {
                return route('group.show', $userpage->item_id);
            }
            return route('front.user.profile', ['id' => $userId]);
        }
    }
 
}
 
 
<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use  App\Modules\Tuongtac\Models\TUserpage;
class TRecommend extends Model
{
    use HasFactory;
    protected $table = 't_recommends'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'user_id',
        'item_id',
        'item_code',
       
        
    ];
    
    public static function hasBookmarked($itemId, $itemCode = 'tblog')
    {
        $userId = \Auth::id(); // ID của người dùng hiện tại

        // Kiểm tra xem bản ghi có tồn tại không
        $bookmarkExists = \DB::table('t_recommends')
            ->where('user_id', $userId)
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->exists();

        return $bookmarkExists;
    }
    
}
 
 
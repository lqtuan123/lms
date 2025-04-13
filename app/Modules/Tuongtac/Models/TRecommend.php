<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TRecommend extends Model
{
    use HasFactory;
    protected $table = 't_recommends';

    protected $fillable = [
        'user_id',
        'item_id',
        'item_code',
    ];

    /**
     * Kiểm tra xem người dùng đã bookmark chưa
     */
    public static function hasBookmarked($itemId, $itemCode)
    {
        $userId = Auth::id();
        if (!$userId) return false;

        return self::where('user_id', $userId)
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->exists();
    }

    /**
     * Thêm bookmark
     */
    public static function addBookmark($itemId, $itemCode)
    {
        return self::firstOrCreate([
            'user_id' => Auth::id(),
            'item_id' => $itemId,
            'item_code' => $itemCode,
        ]);
    }

    /**
     * Xóa bookmark
     */
    public static function removeBookmark($itemId, $itemCode)
    {
        return self::where('user_id', Auth::id())
            ->where('item_id', $itemId)
            ->where('item_code', $itemCode)
            ->delete();
    }

    /**
     * Toggle bookmark
     */
    public static function toggleBookmark($itemId, $itemCode)
    {
        if (self::hasBookmarked($itemId, $itemCode)) {
            self::removeBookmark($itemId, $itemCode);
            return false; // Đã xóa bookmark
        } else {
            self::addBookmark($itemId, $itemCode);
            return true; // Đã thêm bookmark
        }
    }
}

 
 
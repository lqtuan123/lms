<?php

namespace App\Modules\Book\Models;

use App\Models\Rating;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'photo', 'summary', 'content', 'resources', 'status', 'user_id', 'book_type_id', 'views','block', 'average_rating', 'rating_count'];

    protected $casts = [
        'tags' => 'array',
        'resources' => 'array',
        'average_rating' => 'float',
        'rating_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookType(): BelongsTo
    {
        return $this->belongsTo(BookType::class);
    }

    public function incrementViews()
    {
        $this->increment('views');
        $this->save();
    }

    public static function getRecommended($limit = 6)
    {
        return self::orderBy('views', 'desc')
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_books', 'book_id', 'tag_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(\App\Modules\Tuongtac\Models\TRecommend::class, 'item_id', 'id')
            ->where('item_code', 'book');
    }

    /**
     * Lấy tất cả đánh giá của sách
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    
    /**
     * Lấy số lượng đánh giá của sách
     */
    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }
    
    /**
     * Lấy trung bình đánh giá của sách
     */
    public function getAverageRatingAttribute($value)
    {
        // Nếu đã có giá trị lưu trong database, trả về giá trị đó
        if ($value) {
            return $value;
        }
        
        // Nếu không có giá trị, tính toán từ các đánh giá và cập nhật
        $average = $this->ratings()->avg('rating') ?? 0;
        $this->attributes['average_rating'] = $average;
        $this->save();
        
        return $average;
    }
    
    /**
     * Kiểm tra xem người dùng đã đánh giá sách này chưa
     */
    public function userHasRated($userId = null)
    {
        if (!$userId && !Auth::check()) {
            return false;
        }
        
        $userId = $userId ?: Auth::id();
        
        return $this->ratings()->where('user_id', $userId)->exists();
    }
}

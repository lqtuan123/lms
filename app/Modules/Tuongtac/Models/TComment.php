<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Modules\Book\Models\Book;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
        if ($this->status == 'active') {
            $this->status = "inactive";
        } else {
            $this->status = "active";
        }
        $this->save();
    }

    // in TComment.php
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    
    /**
     * Get the parent item that was commented on.
     */
    public function tblog()
    {
        return $this->belongsTo(TBlog::class, 'item_id');
    }
    
    /**
     * Get the book that was commented on.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'item_id');
    }
    
    /**
     * Get the parent item based on item_code.
     */
    public function item()
    {
        switch ($this->item_code) {
            case 'tblog':
                return $this->tblog();
            case 'book':
                return $this->book();
            // Add other cases here as needed
            default:
                return $this->tblog(); // Fallback to prevent null return
        }
    }

    public function replies()
    {
        return $this->hasMany(TComment::class, 'parent_id');
    }

    // Get formatted time difference
    public function getTimeDiffAttribute()
    {
        $createdAt = $this->created_at;
        $now = now();

        if ($createdAt->diffInMinutes($now) < 60) {
            return $createdAt->diffInMinutes($now) . ' phút trước';
        } elseif ($createdAt->diffInHours($now) < 24) {
            return $createdAt->diffInHours($now) . ' giờ trước';
        } else {
            return $createdAt->diffInDays($now) . ' ngày trước';
        }
    }

    /**
     * Get likes for this comment
     */
    public function likes()
    {
        return $this->hasMany(\App\Models\CommentLike::class, 'comment_id');
    }

    /**
     * Check if a user has liked this comment
     * 
     * @param int|null $userId
     * @return bool
     */
    public function isLikedByUser($userId = null)
    {
        if ($userId === null) {
            $userId = \Illuminate\Support\Facades\Auth::id();
        }
        
        if (!$userId) {
            return false;
        }
        
        return $this->likes()->where('user_id', $userId)->exists();
    }
}

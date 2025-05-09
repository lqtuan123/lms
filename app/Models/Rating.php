<?php

namespace App\Models;

use App\Modules\Book\Models\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'book_id',
        'user_id',
        'rating',
        'comment',
    ];

    /**
     * Các trường được ép kiểu
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'float',
    ];

    /**
     * Lấy thông tin sách được đánh giá
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Lấy thông tin người dùng đánh giá
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 
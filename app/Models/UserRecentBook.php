<?php

namespace App\Models;

use App\Modules\Book\Models\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRecentBook extends Model
{
    use HasFactory;

    protected $table = 'user_recent_books'; // Tên bảng

    protected $fillable = [
        'user_id',
        'book_id',
        'read_at',
    ];

    public $timestamps = true; // Bảng có created_at và updated_at

    /**
     * Quan hệ với model User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với model Book
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}

<?php

namespace App\Modules\Book\Models;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Book extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'photo', 'summary', 'content', 'resources', 'status', 'user_id', 'book_type_id', 'views','block'];

    protected $casts = [
        'tags' => 'array',
        'resources' => 'array',
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
}

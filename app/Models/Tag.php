<?php

namespace App\Models;

use App\Modules\Book\Models\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'slug', 'hit', 'status'];
    public function books()
    {
        return $this->belongsToMany(Book::class, 'tag_books', 'tag_id', 'book_id');
    }
}

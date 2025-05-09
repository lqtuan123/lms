<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPrivacySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hide_posts',
        'hide_personal_info',
        'hide_books',
        'hide_favorites'
    ];

    protected $casts = [
        'hide_posts' => 'boolean',
        'hide_personal_info' => 'boolean',
        'hide_books' => 'boolean',
        'hide_favorites' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

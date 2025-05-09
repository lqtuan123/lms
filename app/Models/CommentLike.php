<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    use HasFactory;
    
    protected $table = 't_comment_likes';
    
    protected $fillable = [
        'user_id',
        'comment_id'
    ];
    
    public function comment()
    {
        return $this->belongsTo(\App\Modules\Tuongtac\Models\TComment::class, 'comment_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

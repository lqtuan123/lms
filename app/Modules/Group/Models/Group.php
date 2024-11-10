<?php

namespace App\Modules\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type_code',
        'photo',
        'slug',
        'description',
        'is_private',
        'author_id',
        'status',
      
    ];

   
}

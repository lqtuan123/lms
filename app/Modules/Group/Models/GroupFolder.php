<?php

namespace App\Modules\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupFolder extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'folder_id',
        'is_private',
      
    ];

   
}

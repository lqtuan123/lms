<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TPageItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'page_id',
        'item_id',
        'item_code', 
        'order_id', 
        'status',
    ];
    
    
}
 
 
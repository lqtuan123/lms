<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TOption extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'option_text', 'votes','users','user_id'];

    public function question()
    {
        return $this->belongsTo(TQuestion::class,'question_id');
    }
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id');
        
    }

}
 
 
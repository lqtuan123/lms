<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TSurvey extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'item_id',
        'item_code',
        'expired_date',
        'user_id',
        'user_ids'
        
    ];
    protected $casts = [
        'user_ids' => 'array', // Tự động chuyển đổi JSON thành mảng và ngược lại
    ];
    public function questions()
    {
        return $this->hasMany(TQuestion::class,'survey_id');
    }
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id');
        
    }
    public function hasUserAnswered($userId)
    {
        return is_array($this->user_ids) && in_array($userId, $this->user_ids);
    }
}
 
 
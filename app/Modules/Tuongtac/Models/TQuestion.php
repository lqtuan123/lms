<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'question',
        'survey_id',
        'user_id',
    ];
    public function options()
    {
        return $this->hasMany(TOption::class,'question_id');
    }
    public function surveyGroup()
    {
        return $this->belongsTo(TSurvey::class);
    }
    public function author()
    {
        return $this->belongsTo(\App\Models\User::class,'user_id');
        
    }

}
 
 
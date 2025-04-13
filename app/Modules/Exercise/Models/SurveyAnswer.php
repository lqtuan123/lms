<?php

namespace App\Modules\Exercise\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyAnswer extends Model
{
    protected $fillable = ['response_id', 'question_id', 'answer'];

    public function response()
    {
        return $this->belongsTo(SurveyResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }
}
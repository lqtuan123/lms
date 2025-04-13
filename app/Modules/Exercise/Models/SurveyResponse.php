<?php

namespace App\Modules\Exercise\Models;

use App\Modules\Teaching_1\Models\Student;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = ['survey_id', 'student_id', 'hocphan_id', 'giangvien_id', 'submitted_at'];

    protected $casts = [
        'submitted_at' => 'datetime', // Chuyển submitted_at thành đối tượng Carbon
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class, 'response_id');
    }
}
<?php
namespace App\Modules\Exercise\Models;

use App\Modules\Teaching_1\Models\Student;
use Illuminate\Database\Eloquent\Model;

class TracNghiemSubmission extends Model
{
    protected $table = 'trac_nghiem_submissions';
    protected $fillable = ['student_id', 'assignment_id', 'quiz_id', 'answers', 'score'];
    protected $casts = [
        'answers' => 'array',
        'submitted_at' => 'datetime',
    ];
    public function student()
{
    return $this->belongsTo(Student::class, 'student_id', 'id');
}
}


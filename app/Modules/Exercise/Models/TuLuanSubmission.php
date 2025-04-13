<?php
namespace App\Modules\Exercise\Models;

use App\Modules\Teaching_1\Models\Student;
use Illuminate\Database\Eloquent\Model;

class TuLuanSubmission extends Model
{
    protected $table = 'tu_luan_submissions';
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
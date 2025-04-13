<?php

namespace App\Modules\Teaching_3\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Teaching_3\Models\Enrollment;
use App\Modules\Teaching_1\Models\Student;
use app\Modules\Teaching_3\Models\Enrollment as ModelsEnrollment;

class EnrollResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'enroll_id',
        'student_id',
        'DiemBP',
        'Thi1',
        'Diem1',
        'Thi2',
        'Diem2',
        'DiemMax',
        'DiemChu',
        'DiemHeSo4',
    ];

    /**
     * Relationship with Enrollment.
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enroll_id');
    }

    /**
     * Relationship with Student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}


<?php
namespace App\Modules\Exercise\Models;


use Illuminate\Database\Eloquent\Model;
use App\Modules\Teaching_2\Models\HocPhan; // Import model Module

class Assignment extends Model
{
    protected $table = 'assignments';
    protected $fillable = ['quiz_id', 'quiz_type', 'hocphan_id', 'assigned_at', 'due_date'];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    public function hocphan()
    {
        return $this->belongsTo(HocPhan::class, 'hocphan_id');
    }

    public function quizTracNghiem()
    {
        return $this->belongsTo(BoDeTracNghiem::class, 'quiz_id');
    }

    public function quizTuLuan()
    {
        return $this->belongsTo(BoDeTuLuan::class, 'quiz_id');
    }
}
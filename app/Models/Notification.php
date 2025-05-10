<?php

namespace App\Models;


use App\Models\User;
use App\Modules\Teaching_1\Models\ClassModel;
use App\Modules\Teaching_1\Models\Teacher;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = ['class_id', 'teacher_id', 'title', 'file_path'];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
<?php

namespace App\Modules\Exercise\Models;

use App\Modules\Teaching_1\Models\Teacher;
use App\Modules\Teaching_2\Models\HocPhan;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = ['hocphan_id', 'giangvien_id', 'title', 'description'];

    public function hocphan()
    {
        return $this->belongsTo(HocPhan::class, 'hocphan_id');
    }

    public function giangvien()
    {
        return $this->belongsTo(Teacher::class, 'giangvien_id');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
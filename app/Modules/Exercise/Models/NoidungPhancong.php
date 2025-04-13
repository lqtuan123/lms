<?php
namespace App\Modules\Exercise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Exercise\Models\BoDeTuLuan;
use App\Modules\Exercise\Models\BoDeTracNghiem;
use App\Modules\Teaching_2\Models\PhanCong;

class NoidungPhancong extends Model
{
    use HasFactory;

    protected $table = 'noidung_phancong';

    protected $fillable = [
        'phancong_id',
        'title',
        'slug',
        'content',
        'time_limit',
        'resources',
        'tags',
        'tuluan',
        'tracnghiem'
    ];

    protected $casts = [
        'resources' => 'array',
        'tags' => 'array',
        'tuluan' => 'array',
        'tracnghiem' => 'array',
    ];

    public function phancong()
    {
        return $this->belongsTo(PhanCong::class);
    }

    public function boDeTuLuans()
    {
        return $this->belongsTo(BoDeTuLuan::class );
    }

    public function boDeTracNghiems()
    {
        return $this->belongsTo(BoDeTracNghiem::class);
    }
}

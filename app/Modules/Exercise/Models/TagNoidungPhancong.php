<?php

namespace App\Modules\Exercise\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tag; 
use App\Modules\Exercise\Models\NoidungPhancong;
class TagNoidungPhancong extends Model
{
    use HasFactory;

    protected $table = 'tag_noidungphancong';

    protected $fillable = [
        'tag_id',
        'noidungphancong_id',
    ];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function noidungPhancong()
    {
        return $this->belongsTo(NoidungPhancong::class);
    }
}

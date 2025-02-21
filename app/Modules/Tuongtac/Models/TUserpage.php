<?php

namespace App\Modules\Tuongtac\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TUserpage extends Model
{
    
    use HasFactory;
    protected $table = 't_userpages'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'user_id',
        'point',
        'title',
        'slug',
        'description',
        'banner',
        'status',
    ];
  
    public static function add_points($user_id,$point)
    {
        $user = \App\Models\User::find($user_id);
        if(!$user)
            return 0;
        $userpage = TUserpage::where('user_id',$user_id)->first();
        if(!$userpage)
        {
            $data['user_id'] = $user_id;
            $data['point'] = 0;
            $data['title'] = $user->full_name;
            $slug = Str::slug($user->full_name);
            $slug_count = TUserpage::where('slug',$slug)->count();
            if($slug_count > 0)
            {
                $slug .= time();
            }
            $data['slug'] = $slug;
            $data['description'] ="";
            $data['banner'] = "https://itcctv.vn/images/profile-8.jpg";
            $data['status'] = "active";
            $userpage = TUserpage::create($data);
        }
        $userpage->point +=$point;
        $userpage->save();
        return 1;
    }
    
}
 
 
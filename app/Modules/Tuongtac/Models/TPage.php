<?php

namespace App\Modules\Tuongtac\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TPage extends Model
{
    use HasFactory;
    protected $table = 't_pages'; // Đặt tên bảng theo ý muốn, ví dụ: 'jobs'
    protected $fillable = [
        'title',
        'slug',
        'description', 
        'banner',
        'avatar',
        'item_id',
        'item_code',
        'status',
    ];
    
    public static function getPageUrl($id,$item_code)
    {
        
           
            $page = TPage::where('item_id',$id)->where('item_code',$item_code)->first();
            
            if (!$page)
            {
                if($item_code =="group")
                {
                    $group = Group::find($id);
                    if(!$group)
                        return '';
                    $slug = $group->slug;
                    $ppage = TPage::where('slug',$slug)->first();
                    if($ppage)
                    {
                        $slug .= uniqid();
                    }
                    $data['item_id'] = $id;
                    $data['item_code'] = 'group';
                    $data['title'] = $group->title;
                    $data['slug'] = $slug;
                    $data['description'] ="";
                    $data['banner'] = "https://itcctv.vn/images/profile-8.jpg";
                    $data['avatar'] = "https://itcctv.vn/images/profile-8.jpg";
                    $data['status'] = "active";
                    $page = TPage::create($data);
                }
                if($item_code =="user")
                {

                    $user = User::find($id);
                    if(!$user)
                        return '';
                    $slug = Str::slug($user->full_name);
                    $ppage = TPage::where('slug',$slug)->first();
                    if($ppage)
                    {
                        $slug .= uniqid();
                    }
                    $data['item_id'] = $id;
                    $data['item_code'] = 'user';
                    $data['title'] = $user->full_name;
                    $data['slug'] = $slug;
                    $data['description'] ="";
                    $data['banner'] = "https://itcctv.vn/images/profile-8.jpg";
                    $data['avatar'] = $user->photo?$user->photo:"https://itcctv.vn/images/profile-8.jpg";
                    $data['status'] = "active";
                    $page = TPage::create($data);
                }
                
            }
            
            
            return route('front.tpage.view',$page->slug);
            
        
    }
 
}
 
 
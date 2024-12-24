<?php

namespace App\Modules\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Tuongtac\Models\TPage;
use App\Modules\Group\Models\GroupMemeber;
class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type_code',
        'photo',
        'slug',
        'description',
        'is_private',
        'author_id',
        'status',
      
    ];
    public function getRole($user_id)
    {
        $group = Group::find($this->id);
        $member = GroupMember::where('user_id',$user_id)->where('group_id',$this->id)->first();
        if($member)
            return $member->role;
        else
            return '';
         
       
    }
    public function getPageUrl($id)
    {
        $group = Group::find($id);
        $page = TPage::where('item_id',$id)->where('item_code','group')->first();
        
        if (!$page)
        {
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
        
        
        return route('front.tpage.view',$page->slug);
        
    }
   
}

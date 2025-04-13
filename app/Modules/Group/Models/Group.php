<?php

namespace App\Modules\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Tuongtac\Models\TPage;
use App\Modules\Group\Models\GroupMemeber;
use App\Models\User;
use App\Modules\Tuongtac\Models\TBlog;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type_code',
        'author_id',
        'visibility', // public, private, secret
        'pending_members',
        'members',
        'moderators', // Danh sách ID phó nhóm
        'status',
        'photo',
        'cover_photo',
        'is_private',
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function groupType()
    {
        return $this->belongsTo(GroupType::class, 'type_code', 'type_code');
    }

    public function posts()
    {
        return $this->hasMany(TBlog::class, 'group_id');
    }

    public function ratings()
    {
        return $this->hasOne(\App\Modules\Tuongtac\Models\TVoteItem::class, 'item_id')
                    ->where('item_code', 'group');
    }

    // Helper methods
    public function getMembers()
    {
        return User::whereIn('id', json_decode($this->members ?? '[]', true))->get();
    }

    public function getModerators()
    {
        return User::whereIn('id', json_decode($this->moderators ?? '[]', true))->get();
    }

    public function getPendingMembers()
    {
        return User::whereIn('id', json_decode($this->pending_members ?? '[]', true))->get();
    }

    public function isMember($userId)
    {
        $members = json_decode($this->members ?? '[]', true);
        return in_array($userId, $members);
    }

    public function isAdmin($userId)
    {
        return $this->author_id == $userId;
    }

    public function isModerator($userId)
    {
        $moderators = json_decode($this->moderators ?? '[]', true);
        return in_array($userId, $moderators);
    }

    public function hasManagementRights($userId)
    {
        return $this->isAdmin($userId) || $this->isModerator($userId);
    }

    public function addMember($userId)
    {
        $members = json_decode($this->members ?? '[]', true);
        if (!in_array($userId, $members)) {
            $members[] = $userId;
            $this->members = json_encode(array_values($members));
            $this->save();
            
            // Xóa khỏi danh sách đang chờ nếu có
            $this->removePendingMember($userId);
            return true;
        }
        return false;
    }

    public function removeMember($userId)
    {
        $members = json_decode($this->members ?? '[]', true);
        if (($key = array_search($userId, $members)) !== false) {
            unset($members[$key]);
            $this->members = json_encode(array_values($members));
            
            // Đồng thời xóa khỏi danh sách moderator nếu có
            $this->removeModerator($userId);
            
            $this->save();
            return true;
        }
        return false;
    }

    public function addModerator($userId)
    {
        // Chỉ thêm moderator nếu người dùng đó là thành viên
        if (!$this->isMember($userId)) {
            return false;
        }
        
        $moderators = json_decode($this->moderators ?? '[]', true);
        if (!in_array($userId, $moderators)) {
            $moderators[] = $userId;
            $this->moderators = json_encode(array_values($moderators));
            $this->save();
            return true;
        }
        return false;
    }

    public function removeModerator($userId)
    {
        $moderators = json_decode($this->moderators ?? '[]', true);
        if (($key = array_search($userId, $moderators)) !== false) {
            unset($moderators[$key]);
            $this->moderators = json_encode(array_values($moderators));
            $this->save();
            return true;
        }
        return false;
    }

    public function addPendingMember($userId)
    {
        $pending = json_decode($this->pending_members ?? '[]', true);
        if (!in_array($userId, $pending)) {
            $pending[] = $userId;
            $this->pending_members = json_encode(array_values($pending));
            $this->save();
            return true;
        }
        return false;
    }

    public function removePendingMember($userId)
    {
        $pending = json_decode($this->pending_members ?? '[]', true);
        if (($key = array_search($userId, $pending)) !== false) {
            unset($pending[$key]);
            $this->pending_members = json_encode(array_values($pending));
            $this->save();
            return true;
        }
        return false;
    }

    public function getMemberCount()
    {
        $members = json_decode($this->members ?? '[]', true);
        return count($members);
    }

    public function getPendingCount()
    {
        $pending = json_decode($this->pending_members ?? '[]', true);
        return count($pending);
    }

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

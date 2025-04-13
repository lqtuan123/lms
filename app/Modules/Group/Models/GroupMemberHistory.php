<?php

namespace App\Modules\Group\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class GroupMemberHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'action',      // joined, left, removed, promoted, demoted
        'performed_by', // ID người thực hiện hành động (admin/moderator)
        'role_before',  // Vai trò trước khi thay đổi
        'role_after',   // Vai trò sau khi thay đổi
        'note',         // Ghi chú
        'created_at',
        'updated_at'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Các phương thức tiện ích
    public static function recordJoin($groupId, $userId, $performedBy = null, $note = null)
    {
        return self::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action' => 'joined',
            'performed_by' => $performedBy ?? $userId,
            'role_before' => null,
            'role_after' => 'member',
            'note' => $note
        ]);
    }

    public static function recordLeave($groupId, $userId, $performedBy = null, $note = null)
    {
        return self::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action' => 'left',
            'performed_by' => $performedBy ?? $userId,
            'role_before' => 'member',
            'role_after' => null,
            'note' => $note
        ]);
    }

    public static function recordRemoval($groupId, $userId, $performedBy, $note = null)
    {
        return self::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action' => 'removed',
            'performed_by' => $performedBy,
            'role_before' => 'member',
            'role_after' => null,
            'note' => $note
        ]);
    }

    public static function recordPromotion($groupId, $userId, $performedBy, $roleBefore = 'member', $roleAfter = 'moderator', $note = null)
    {
        return self::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action' => 'promoted',
            'performed_by' => $performedBy,
            'role_before' => $roleBefore,
            'role_after' => $roleAfter,
            'note' => $note
        ]);
    }

    public static function recordDemotion($groupId, $userId, $performedBy, $roleBefore = 'moderator', $roleAfter = 'member', $note = null)
    {
        return self::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'action' => 'demoted',
            'performed_by' => $performedBy,
            'role_before' => $roleBefore,
            'role_after' => $roleAfter,
            'note' => $note
        ]);
    }
} 
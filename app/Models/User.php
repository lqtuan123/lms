<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Modules\Tuongtac\Models\TBlog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'global_id',
        'full_name',
        'username',
        'email',
        'password',
        'email_verified_at',
        'photo',
        'banner',
        'phone',
        'address',
        'description',
        'google_id',
        'facebook_id',
        'social_avatar',
        'ugroup_id',
        'role',
        'totalpoint',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function deleteUser($user_id)
    {
        $user = User::find($user_id);
        if (Auth::user()->role == 'admin') {
            $user->delete();
            return 1;
        } else {
            $user->status = "inactive";
            $user->save();
            return 0;
        }

    }
    public static function c_create($data)
    {
        $pro = User::create($data);
        $pro->code = "CUS" . sprintf('%09d', $pro->id);
        $pro->save();

        return $pro;
    }
    
    /**
     * Get the user's name.
     * If name attribute doesn't exist, returns full_name
     */
    public function getNameAttribute($value)
    {
        // If there's a name value, return it
        if (!empty($value)) {
            return $value;
        }
        
        // Otherwise return full_name
        return $this->full_name;
    }

    public function blogs()
    {
        return $this->hasMany(TBlog::class, 'user_id', 'id');
    }

    /**
     * Relationship với bảng group_members
     */
    public function groupMember()
    {
        return $this->hasOne(\App\Modules\Group\Models\GroupMember::class, 'user_id', 'id');
    }

    /**
     * Relationship với bảng point_histories
     */
    public function pointHistories()
    {
        return $this->hasMany(PointHistory::class);
    }

    /**
     * Cộng điểm cho người dùng dựa trên hoạt động
     * 
     * @param string $pointCode Mã hoạt động
     * @param int|null $referenceId ID tham chiếu
     * @param string|null $referenceType Loại tham chiếu
     * @param string|null $description Mô tả
     * @return PointHistory|null
     */
    public function addPoint($pointCode, $referenceId = null, $referenceType = null, $description = null)
    {
        // Tìm quy tắc điểm
        $pointRule = PointRule::where('code', $pointCode)->where('status', 'active')->first();
        
        if (!$pointRule) {
            return null;
        }

        // Tạo lịch sử điểm
        $pointHistory = PointHistory::create([
            'user_id' => $this->id,
            'point_rule_id' => $pointRule->id,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'point' => $pointRule->point_value,
            'description' => $description ?? $pointRule->description,
            'status' => 'active'
        ]);

        // Cập nhật tổng điểm
        $this->totalpoint = $this->totalpoint + $pointRule->point_value;
        $this->save();

        return $pointHistory;
    }

    /**
     * Hủy điểm một giao dịch điểm cụ thể
     * 
     * @param int $pointHistoryId ID của lịch sử điểm
     * @return bool
     */
    public function cancelPoint($pointHistoryId)
    {
        $pointHistory = PointHistory::where('id', $pointHistoryId)
            ->where('user_id', $this->id)
            ->where('status', 'active')
            ->first();
            
        if (!$pointHistory) {
            return false;
        }

        // Hủy điểm
        $pointHistory->status = 'canceled';
        $pointHistory->save();

        // Cập nhật lại tổng điểm
        $this->totalpoint = $this->totalpoint - $pointHistory->point;
        $this->save();

        return true;
    }

    /**
     * Tính toán lại tổng điểm dựa trên lịch sử
     * 
     * @return int
     */
    public function recalculateTotalPoints()
    {
        $totalPoints = $this->pointHistories()
            ->where('status', 'active')
            ->sum('point');
            
        $this->totalpoint = $totalPoints;
        $this->save();
        
        return $totalPoints;
    }

    /**
     * Tính lại tổng điểm cho tất cả người dùng
     * 
     * @return array
     */
    public static function recalculateAllUserPoints()
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'total' => 0
        ];
        
        $users = self::all();
        $results['total'] = count($users);
        
        foreach ($users as $user) {
            try {
                $user->recalculateTotalPoints();
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }
        
        return $results;
    }

    /**
     * Đếm số thông báo chưa đọc của người dùng
     *
     * @return int
     */
    public function unreadNotificationsCount()
    {
        return \App\Modules\Tuongtac\Models\TNotice::where('user_id', $this->id)
            ->where('seen', 1)
            ->count();
    }

    public function privacySettings()
    {
        return $this->hasOne(\App\Models\UserPrivacySetting::class);
    }
}



<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Str;

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
        'phone',
        'address',
        'description',

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
        if (auth()->user()->role == 'admin') {
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
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'poll_id',
        'option_id',
        'user_id',
    ];

    /**
     * Get the poll that this vote belongs to.
     */
    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Get the option that was voted for.
     */
    public function option()
    {
        return $this->belongsTo(PollOption::class);
    }

    /**
     * Get the user who cast this vote.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Poll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'question',
        'group_id',
        'expires_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the options for the poll.
     */
    public function options()
    {
        return $this->hasMany(PollOption::class);
    }

    /**
     * Get the votes for the poll.
     */
    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Get the user who created the poll.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the group that the poll belongs to.
     * Phương thức này có thể được sửa đổi hoặc xóa tùy thuộc vào cấu trúc của ứng dụng
     */
    public function group()
    {
        // Đảm bảo trả về một đối tượng relationship, không trả về null
        if (class_exists('App\Modules\Group\Models\Group')) {
            return $this->belongsTo('App\Modules\Group\Models\Group', 'group_id');
        }
        
        return $this->belongsTo('App\Models\Group', 'group_id');
    }

    /**
     * Check if a user has voted on this poll.
     */
    public function hasUserVoted($userId)
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the poll has expired.
     */
    public function isExpired()
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Get the total number of votes.
     */
    public function getTotalVotesCount()
    {
        return $this->votes()->count();
    }

    /**
     * Get the count of votes for each option along with percentage.
     */
    public function getVotesCountByOption()
    {
        $totalVotes = $this->getTotalVotesCount();
        $results = [];
        
        // Lấy tất cả các lựa chọn của poll với eager loading votes
        $options = $this->options()->with('votes')->get();
        
        // Debug log để kiểm tra dữ liệu
        \Illuminate\Support\Facades\Log::info('Poll getVotesCountByOption', [
            'poll_id' => $this->id,
            'total_votes' => $totalVotes,
            'options_count' => $options->count()
        ]);
        
        foreach ($options as $option) {
            // Đếm votes từ relationship đã được eager load
            $voteCount = $option->votes->count();
            $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 1) : 0;
            
            \Illuminate\Support\Facades\Log::info('Option data', [
                'option_id' => $option->id,
                'option_text' => $option->option_text,
                'votes_count' => $voteCount,
                'percentage' => $percentage
            ]);
            
            $results[] = [
                'id' => $option->id,
                'text' => $option->option_text,
                'count' => $voteCount,
                'percentage' => $percentage
            ];
        }
        
        return $results;
    }
}

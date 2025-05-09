<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PollPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // Tất cả người dùng đã đăng nhập có thể xem danh sách khảo sát
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Poll $poll)
    {
        return true; // Tất cả người dùng đã đăng nhập có thể xem chi tiết khảo sát
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true; // Tất cả người dùng đã đăng nhập có thể tạo khảo sát
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Poll $poll)
    {
        return $user->id === $poll->created_by; // Chỉ người tạo mới có thể cập nhật
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Poll $poll)
    {
        return $user->id === $poll->created_by; // Chỉ người tạo mới có thể xóa
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Poll $poll)
    {
        return false; // Không hỗ trợ khôi phục khảo sát đã xóa
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Poll $poll)
    {
        return false; // Không hỗ trợ xóa vĩnh viễn
    }

    /**
     * Determine whether the user can vote on the poll.
     */
    public function vote(User $user, Poll $poll)
    {
        // Kiểm tra nếu người dùng đã bình chọn hoặc khảo sát đã hết hạn
        return !$poll->hasUserVoted($user->id) && !$poll->isExpired();
    }
}

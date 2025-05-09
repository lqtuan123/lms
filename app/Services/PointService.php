<?php

namespace App\Services;

use App\Models\PointHistory;
use App\Models\PointRule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Cộng điểm cho user
     *
     * @param int $userId ID của người dùng
     * @param string $pointCode Mã hoạt động
     * @param int|null $referenceId ID tham chiếu
     * @param string|null $referenceType Loại tham chiếu
     * @param string|null $description Mô tả
     * @return PointHistory|null
     */
    public function addPoint($userId, $pointCode, $referenceId = null, $referenceType = null, $description = null)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return $user->addPoint($pointCode, $referenceId, $referenceType, $description);
    }

    /**
     * Hủy điểm một giao dịch điểm cụ thể
     *
     * @param int $userId ID của người dùng
     * @param int $pointHistoryId ID của lịch sử điểm
     * @return bool
     */
    public function cancelPoint($userId, $pointHistoryId)
    {
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        return $user->cancelPoint($pointHistoryId);
    }

    /**
     * Lấy lịch sử điểm của người dùng
     *
     * @param int $userId ID của người dùng
     * @param int $limit Số bản ghi mỗi trang
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserPointHistory($userId, $limit = 15)
    {
        return PointHistory::with(['pointRule'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Lấy tất cả quy tắc tính điểm
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPointRules()
    {
        return PointRule::orderBy('name')->get();
    }

    /**
     * Cập nhật quy tắc tính điểm
     *
     * @param int $ruleId ID của quy tắc
     * @param array $data Dữ liệu cập nhật
     * @return PointRule|null
     */
    public function updatePointRule($ruleId, array $data)
    {
        $rule = PointRule::find($ruleId);
        if (!$rule) {
            return null;
        }

        $rule->update($data);
        return $rule;
    }

    /**
     * Tạo quy tắc tính điểm mới
     *
     * @param array $data Dữ liệu tạo mới
     * @return PointRule
     */
    public function createPointRule(array $data)
    {
        return PointRule::create($data);
    }

    /**
     * Tính toán lại tổng điểm cho người dùng
     *
     * @param int $userId ID của người dùng
     * @return int|null
     */
    public function recalculateUserPoints($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return $user->recalculateTotalPoints();
    }

    /**
     * Báo cáo tổng hợp điểm theo loại hoạt động
     *
     * @return array
     */
    public function getPointsSummaryByActivity()
    {
        return DB::table('point_histories')
            ->join('point_rules', 'point_histories.point_rule_id', '=', 'point_rules.id')
            ->where('point_histories.status', 'active')
            ->select('point_rules.name', 'point_rules.code', DB::raw('COUNT(*) as total_transactions'), DB::raw('SUM(point_histories.point) as total_points'))
            ->groupBy('point_rules.id', 'point_rules.name', 'point_rules.code')
            ->orderBy('total_points', 'desc')
            ->get();
    }

    /**
     * Báo cáo top người dùng có điểm cao nhất
     *
     * @param int $limit Số lượng người dùng
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopUsersByPoints($limit = 10)
    {
        return User::where('status', 'active')
            ->orderBy('totalpoint', 'desc')
            ->take($limit)
            ->get();
    }
} 
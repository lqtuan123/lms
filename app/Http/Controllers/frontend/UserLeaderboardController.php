<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PointHistory;
use App\Modules\Book\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class UserLeaderboardController extends Controller
{
    /**
     * Hiển thị trang vinh danh bạn đọc (leaderboard)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Lấy top 20 người dùng có điểm cao nhất
        // Sử dụng totalpoint từ bảng users thay vì tính tổng từ point_histories
        $topUsers = User::select('users.id', 'users.full_name', 'users.photo', 'users.email', 'users.totalpoint as total_points')
            ->where('users.status', 'active')
            ->where('users.totalpoint', '>', 0)
            ->orderBy('totalpoint', 'desc')
            ->limit(20)
            ->get();
        
        // Lấy top người dùng theo tuần
        $weekStart = now()->subDays(7)->startOfDay();
        
        // Truy vấn 1: Lấy tổng điểm từ point_histories trong tuần
        $weeklyPointsQuery = DB::table('point_histories')
            ->select('user_id', DB::raw('SUM(point) as period_points'))
            ->where('status', 'active')
            ->where('created_at', '>=', $weekStart)
            ->groupBy('user_id');
            
        // Tạo bảng tạm thời từ subquery
        $weeklyTopUsers = User::select('users.id', 'users.full_name', 'users.photo', 'users.email')
            ->joinSub($weeklyPointsQuery, 'weekly_points', function($join) {
                $join->on('users.id', '=', 'weekly_points.user_id');
            })
            ->addSelect('weekly_points.period_points as total_points')
            ->where('users.status', 'active')
            ->orderBy('total_points', 'desc')
            ->limit(10)
            ->get();
        
        // Lấy top người dùng theo tháng
        $monthStart = now()->subDays(30)->startOfDay();
        
        // Truy vấn 1: Lấy tổng điểm từ point_histories trong tháng
        $monthlyPointsQuery = DB::table('point_histories')
            ->select('user_id', DB::raw('SUM(point) as period_points'))
            ->where('status', 'active')
            ->where('created_at', '>=', $monthStart)
            ->groupBy('user_id');
            
        // Tạo bảng tạm thời từ subquery
        $monthlyTopUsers = User::select('users.id', 'users.full_name', 'users.photo', 'users.email')
            ->joinSub($monthlyPointsQuery, 'monthly_points', function($join) {
                $join->on('users.id', '=', 'monthly_points.user_id');
            })
            ->addSelect('monthly_points.period_points as total_points')
            ->where('users.status', 'active')
            ->orderBy('total_points', 'desc')
            ->limit(10)
            ->get();

        // Lấy số sách đã đọc cho mỗi người dùng
        $allUsers = collect();
        $allUsers = $allUsers->merge($topUsers)->merge($weeklyTopUsers)->merge($monthlyTopUsers)->unique('id');
        
        // Tạo một danh sách các ID người dùng để thực hiện truy vấn một lần
        $userIds = $allUsers->pluck('id')->toArray();
        
        // Lấy dữ liệu số sách đã đọc cho tất cả người dùng một lần
        $booksReadCounts = DB::table('point_histories')
            ->select('user_id', DB::raw('COUNT(DISTINCT reference_id) as books_read_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where(function($query) {
                $query->where('reference_type', 'book')
                    ->orWhereExists(function($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('point_rules')
                            ->whereRaw('point_histories.point_rule_id = point_rules.id')
                            ->where('point_rules.code', 'read_book');
                    });
            })
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
            
        // Lấy dữ liệu số sách đã đọc theo tuần
        $weeklyBooksReadCounts = DB::table('point_histories')
            ->select('user_id', DB::raw('COUNT(DISTINCT reference_id) as books_read_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where('created_at', '>=', $weekStart)
            ->where(function($query) {
                $query->where('reference_type', 'book')
                    ->orWhereExists(function($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('point_rules')
                            ->whereRaw('point_histories.point_rule_id = point_rules.id')
                            ->where('point_rules.code', 'read_book');
                    });
            })
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
            
        // Lấy dữ liệu số sách đã đọc theo tháng
        $monthlyBooksReadCounts = DB::table('point_histories')
            ->select('user_id', DB::raw('COUNT(DISTINCT reference_id) as books_read_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where('created_at', '>=', $monthStart)
            ->where(function($query) {
                $query->where('reference_type', 'book')
                    ->orWhereExists(function($subquery) {
                        $subquery->select(DB::raw(1))
                            ->from('point_rules')
                            ->whereRaw('point_histories.point_rule_id = point_rules.id')
                            ->where('point_rules.code', 'read_book');
                    });
            })
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
        
        // Gán số sách đã đọc vào từng bộ dữ liệu người dùng
        foreach ($topUsers as $user) {
            $user->books_read_count = $booksReadCounts[$user->id]->books_read_count ?? 0;
        }

        foreach ($weeklyTopUsers as $user) {
            $user->books_read_count = $weeklyBooksReadCounts[$user->id]->books_read_count ?? 0;
        }
        
        foreach ($monthlyTopUsers as $user) {
            $user->books_read_count = $monthlyBooksReadCounts[$user->id]->books_read_count ?? 0;
        }

        // Lấy thêm thông tin chi tiết về các hoạt động tích điểm cho tất cả
        $userActivities = DB::table('point_histories')
            ->select('user_id', 'reference_type', DB::raw('COUNT(*) as activity_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->groupBy('user_id', 'reference_type')
            ->get();
            
        // Lấy thêm thông tin chi tiết về các hoạt động tích điểm theo tuần
        $weeklyUserActivities = DB::table('point_histories')
            ->select('user_id', 'reference_type', DB::raw('COUNT(*) as activity_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where('created_at', '>=', $weekStart)
            ->groupBy('user_id', 'reference_type')
            ->get();
            
        // Lấy thêm thông tin chi tiết về các hoạt động tích điểm theo tháng
        $monthlyUserActivities = DB::table('point_histories')
            ->select('user_id', 'reference_type', DB::raw('COUNT(*) as activity_count'))
            ->whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->where('created_at', '>=', $monthStart)
            ->groupBy('user_id', 'reference_type')
            ->get();

        // Tạo mảng lưu trữ thông tin hoạt động theo người dùng
        $userActivityData = [];
        $weeklyUserActivityData = [];
        $monthlyUserActivityData = [];
        
        foreach ($userActivities as $activity) {
            if (!isset($userActivityData[$activity->user_id])) {
                $userActivityData[$activity->user_id] = [];
            }
            $userActivityData[$activity->user_id][$activity->reference_type] = $activity->activity_count;
        }
        
        foreach ($weeklyUserActivities as $activity) {
            if (!isset($weeklyUserActivityData[$activity->user_id])) {
                $weeklyUserActivityData[$activity->user_id] = [];
            }
            $weeklyUserActivityData[$activity->user_id][$activity->reference_type] = $activity->activity_count;
        }
        
        foreach ($monthlyUserActivities as $activity) {
            if (!isset($monthlyUserActivityData[$activity->user_id])) {
                $monthlyUserActivityData[$activity->user_id] = [];
            }
            $monthlyUserActivityData[$activity->user_id][$activity->reference_type] = $activity->activity_count;
        }

        // Gán thông tin hoạt động cho người dùng
        foreach ($topUsers as $user) {
            $user->activities = $userActivityData[$user->id] ?? [];
        }
        
        foreach ($weeklyTopUsers as $user) {
            $user->activities = $weeklyUserActivityData[$user->id] ?? [];
        }
        
        foreach ($monthlyTopUsers as $user) {
            $user->activities = $monthlyUserActivityData[$user->id] ?? [];
        }

        return view('frontend.leaderboard.index', compact('topUsers', 'weeklyTopUsers', 'monthlyTopUsers'));
    }
} 
<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PointHistory;
use App\Models\PointRule;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Hiển thị trang quản lý quy tắc điểm
     */
    public function index()
    {
        $pointRules = $this->pointService->getAllPointRules();
        $active_menu = 'point_rules';
        
        return view('backend.points.index', compact('pointRules', 'active_menu'));
    }

    /**
     * Hiển thị trang tạo quy tắc điểm mới
     */
    public function create()
    {
        $active_menu = 'point_rules';
        return view('backend.points.create', compact('active_menu'));
    }

    /**
     * Lưu quy tắc điểm mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:point_rules,code',
            'description' => 'nullable|string',
            'point_value' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $this->pointService->createPointRule($validated);

        return redirect()->route('admin.points.index')->with('success', 'Quy tắc điểm đã được tạo thành công!');
    }

    /**
     * Hiển thị trang chỉnh sửa quy tắc điểm
     */
    public function edit($id)
    {
        $pointRule = PointRule::findOrFail($id);
        $active_menu = 'point_rules';
        
        return view('backend.points.edit', compact('pointRule', 'active_menu'));
    }

    /**
     * Cập nhật quy tắc điểm
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point_value' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $this->pointService->updatePointRule($id, $validated);

        return redirect()->route('admin.points.index')->with('success', 'Quy tắc điểm đã được cập nhật thành công!');
    }

    /**
     * Hiển thị trang lịch sử điểm của người dùng
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);
        $pointHistory = $this->pointService->getUserPointHistory($userId);
        $active_menu = 'user_points';
        
        return view('backend.points.user-history', compact('user', 'pointHistory', 'active_menu'));
    }

    /**
     * Tính lại điểm cho người dùng
     */
    public function recalculateUserPoints($userId)
    {
        $user = User::findOrFail($userId);
        $newTotal = $this->pointService->recalculateUserPoints($userId);
        
        return redirect()->back()->with('success', "Đã tính lại điểm cho người dùng {$user->full_name}. Tổng điểm mới: {$newTotal}");
    }

    /**
     * Hủy một giao dịch điểm
     */
    public function cancelPointTransaction($pointHistoryId)
    {
        $pointHistory = PointHistory::findOrFail($pointHistoryId);
        $result = $this->pointService->cancelPoint($pointHistory->user_id, $pointHistoryId);
        
        if ($result) {
            return redirect()->back()->with('success', 'Đã hủy giao dịch điểm thành công');
        }
        
        return redirect()->back()->with('error', 'Không thể hủy giao dịch điểm');
    }

    /**
     * Hiển thị báo cáo điểm
     */
    public function reports()
    {
        $summaryByActivity = $this->pointService->getPointsSummaryByActivity();
        $topUsers = $this->pointService->getTopUsersByPoints();
        $active_menu = 'point_reports';
        
        return view('backend.points.reports', compact('summaryByActivity', 'topUsers', 'active_menu'));
    }

    /**
     * Thêm điểm trực tiếp cho người dùng (thủ công)
     */
    public function addManualPoints(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'point_rule_id' => 'required|exists:point_rules,id',
            'description' => 'nullable|string',
        ]);
        
        $pointRule = PointRule::find($request->point_rule_id);
        $user = User::find($request->user_id);
        
        $pointHistory = PointHistory::create([
            'user_id' => $validated['user_id'],
            'point_rule_id' => $validated['point_rule_id'],
            'point' => $pointRule->point_value,
            'description' => $validated['description'] ?? $pointRule->description,
            'status' => 'active'
        ]);
        
        // Cập nhật tổng điểm
        $user->totalpoint = $user->totalpoint + $pointRule->point_value;
        $user->save();
        
        return redirect()->back()->with('success', "Đã thêm {$pointRule->point_value} điểm cho người dùng {$user->full_name}.");
    }
} 
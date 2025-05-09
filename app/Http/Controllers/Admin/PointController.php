<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PointHistory;
use App\Models\PointRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class PointController extends Controller
{
    /**
     * Hiển thị danh sách người dùng và điểm số
     */
    public function index()
    {
        $users = User::select('id', 'full_name', 'email', 'totalpoint', 'status')
            ->where('status', 'active')
            ->orderBy('totalpoint', 'desc')
            ->paginate(20);
            
        return view('admin.points.index', compact('users'));
    }
    
    /**
     * Hiển thị lịch sử điểm của người dùng
     */
    public function userHistory($id)
    {
        $user = User::findOrFail($id);
        
        $pointHistories = PointHistory::where('user_id', $id)
            ->with('pointRule')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.points.history', compact('user', 'pointHistories'));
    }
    
    /**
     * Hiển thị form thêm điểm cho người dùng
     */
    public function create()
    {
        $users = User::where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email']);
            
        $pointRules = PointRule::where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.points.create', compact('users', 'pointRules'));
    }
    
    /**
     * Thêm điểm cho người dùng
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'point_rule_id' => 'required|exists:point_rules,id',
            'point' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer'
        ]);
        
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($request->user_id);
            $pointRule = PointRule::findOrFail($request->point_rule_id);
            
            // Sử dụng điểm từ request hoặc từ quy tắc
            $point = $request->has('point') ? $request->point : $pointRule->point_value;
            
            // Tạo lịch sử điểm
            $pointHistory = PointHistory::create([
                'user_id' => $user->id,
                'point_rule_id' => $pointRule->id,
                'reference_id' => $request->reference_id,
                'reference_type' => $request->reference_type,
                'point' => $point,
                'description' => $request->description ?? $pointRule->description,
                'status' => 'active'
            ]);
            
            // Cập nhật tổng điểm
            $user->totalpoint = $user->totalpoint + $point;
            $user->save();
            
            DB::commit();
            
            return redirect()->route('admin.points.index')
                ->with('success', "Đã thêm {$point} điểm cho người dùng {$user->full_name}");
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Hủy giao dịch điểm
     */
    public function cancel($id)
    {
        try {
            DB::beginTransaction();
            
            $pointHistory = PointHistory::findOrFail($id);
            
            if ($pointHistory->status === 'canceled') {
                return redirect()->back()
                    ->with('warning', 'Giao dịch điểm này đã bị hủy trước đó');
            }
            
            $user = User::findOrFail($pointHistory->user_id);
            
            // Hủy giao dịch điểm
            $pointHistory->status = 'canceled';
            $pointHistory->save();
            
            // Cập nhật tổng điểm
            $user->totalpoint = $user->totalpoint - $pointHistory->point;
            $user->save();
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Đã hủy giao dịch điểm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    /**
     * Tính toán lại điểm cho tất cả người dùng
     */
    public function recalculateAll()
    {
        try {
            $exitCode = Artisan::call('users:recalculate-points');
            
            if ($exitCode === 0) {
                return redirect()->route('admin.points.index')
                    ->with('success', 'Đã tính toán lại điểm cho tất cả người dùng thành công');
            } else {
                return redirect()->route('admin.points.index')
                    ->with('error', 'Đã xảy ra lỗi khi tính toán lại điểm');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.points.index')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    
    /**
     * Tính toán lại điểm cho một người dùng cụ thể
     */
    public function recalculateUser($id)
    {
        try {
            $exitCode = Artisan::call('users:recalculate-points', [
                '--user-id' => $id
            ]);
            
            if ($exitCode === 0) {
                return redirect()->route('admin.points.user-history', $id)
                    ->with('success', 'Đã tính toán lại điểm cho người dùng thành công');
            } else {
                return redirect()->route('admin.points.user-history', $id)
                    ->with('error', 'Đã xảy ra lỗi khi tính toán lại điểm');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.points.user-history', $id)
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
} 
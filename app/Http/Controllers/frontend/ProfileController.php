<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SettingDetail;
use App\Models\Links;

class ProfileController extends Controller
{
    protected $front_view = 'frontend';

    public function __construct()
    {
        $this->middleware('auth');


    }

    private function loadCommonData($title)
    {
        return [
            'detail' => SettingDetail::find(1),
            'pagetitle' => $title,
            'profile' => auth()->user()
        ];
    }

    public function order()
    {
        $data = $this->loadCommonData("Thông tin tài khoản");
        return view("{$this->front_view}.profile.view", $data);
    }

    public function viewWishlist()
    {
        $data = $this->loadCommonData("Sản phẩm yêu thích");
        $data['links'] = [(new Links(['title' => 'Danh sách sản phẩm yêu thích', 'url' => '#']))];
        return view("{$this->front_view}.profile.wishlist", $data);
    }

    public function viewDashboard()
    {
        $data = $this->loadCommonData("Thông tin tài khoản");
        $data['links'] = [(new Links(['title' => 'Thông tin tài khoản', 'url' => '#']))];
        return view("{$this->front_view}.profile.view", $data);
    }

    public function createEdit()
    {
        $data = $this->loadCommonData("Điều chỉnh thông tin tài khoản");
        return view("{$this->front_view}.profile.edit", $data);
    }

    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'full_name' => 'string|required',
                'address' => 'string|required',
                'photo' => 'string|nullable',
                'description' => 'string|nullable',
            ]);

            $user = auth()->user();
            $data = $request->except('photo');

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $data['photo'] = $this->storeFile($file, 'avatar', true, $filename);
            } elseif ($request->photo) {
                $data['photo'] = $request->photo;
            }            

            if ($user->update($data)) {
                return back()->withSuccess('Bạn đã cập nhật thành công');
            } else {
                Log::error("Update profile failed for user ID: {$user->id}");
                return back()->withError('Lỗi xảy ra khi cập nhật hồ sơ.');
            }
        } catch (\Exception $e) {
            Log::error("Update profile error: " . $e->getMessage());
            return back()->withError('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|confirmed|min:8|string'
            ]);

            $user = auth()->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withError("Mật khẩu hiện tại không đúng");
            }

            if ($request->current_password === $request->new_password) {
                return back()->withError("Mật khẩu mới không được trùng với mật khẩu cũ");
            }

            $user->update(['password' => Hash::make($request->new_password)]);
            return back()->withSuccess("Đổi mật khẩu thành công");
        } catch (\Exception $e) {
            Log::error("Change password error: " . $e->getMessage());
            return back()->withError('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }

    public function updateName(Request $request)
    {
        return $this->updateUserField($request, ['full_name', 'address']);
    }

    public function updateDescription(Request $request)
    {
        return $this->updateUserField($request, ['description'], ['description' => 'string|nullable']);
    }

    private function updateUserField(Request $request, array $fields, $rules = [])
    {
        try {
            $defaultRules = array_fill_keys($fields, 'string|required');
            $validatedData = $request->validate(array_merge($defaultRules, $rules));

            $user = auth()->user();
            if ($user->update($validatedData)) {
                return back()->withSuccess('Cập nhật thành công');
            } else {
                Log::error("Update failed for user ID: {$user->id}");
                return back()->withError('Lỗi xảy ra');
            }
        } catch (\Exception $e) {
            Log::error("Update field error: " . $e->getMessage());
            return back()->withError('Lỗi hệ thống. Vui lòng thử lại sau.');
        }
    }
}

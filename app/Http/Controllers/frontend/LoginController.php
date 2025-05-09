<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'resetPassword']);
    }

    public function viewLogin()
    {
        // dd(auth()->user());
        return view('frontend.auth.login');
    }
    public function logout(Request $request)
    {
        // Log the user out of the application
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the session token to prevent session fixation attacks
        $request->session()->regenerateToken();

        // Lấy URL hiện tại từ form gửi lên, nếu không có thì về trang chủ
        $redirectUrl = $request->input('redirect');
        
        if ($redirectUrl) {
            return redirect($redirectUrl);
        }
        
        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',

        ]);

        if (Auth::user()) {
            return redirect()->route('/');
        }
        $olduser = User::where('phone', $request->email)->orWhere('email', $request->email)->first();
        if ($olduser) {
            if (Auth::attempt(array('email' => $olduser->email, 'password' => $request->password))) {
                if (Auth::user()) {
                    if (isset($request->plink) && $request->plink != '') {
                        return redirect($request->plink);
                    } else
                        return redirect()->route('admin.home');
                }
            } else {
                return back()->with('error', 'Email hoặc số điện thoại hoặc mật khẩu không đúng.');
            }
        } else {
            return back()->with('error', 'Email hoặc số điện thoại hoặc mật khẩu không đúng.');
        }
    }

    public function credentials(Request $request)
    {

        return ['email' => $request->email, 'password' => $request->password, 'status' => 'active'];
    }
    public function viewAdminlogin()
    {
        return view('auth.admin.login');
    }
    
    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPasswordForm()
    {
        return view('frontend.auth.forgot-password');
    }
    
    /**
     * Xử lý yêu cầu đặt lại mật khẩu
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Vui lòng nhập email của bạn',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.exists' => 'Email này không tồn tại trong hệ thống',
        ]);

        // Xóa các token cũ nếu có
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Tạo token ngẫu nhiên
        $token = Str::random(64);

        // Lưu thông tin token vào bảng password_reset_tokens
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Gửi email đặt lại mật khẩu
        Mail::send('frontend.auth.email.reset-password', ['token' => $token, 'email' => $request->email], function($message) use($request){
            $message->to($request->email);
            $message->subject('Yêu cầu đặt lại mật khẩu');
        });

        return back()->with('success', 'Chúng tôi đã gửi email liên kết đặt lại mật khẩu đến địa chỉ email của bạn!');
    }
    
    /**
     * Hiển thị form đặt lại mật khẩu
     */
    public function showResetPasswordForm($token)
    {
        // Kiểm tra token có tồn tại không
        $passwordReset = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();
            
        if (!$passwordReset) {
            return redirect()->route('front.login')->with('error', 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!');
        }
        
        // Kiểm tra thời gian tạo token có quá 60 phút không
        $tokenCreatedAt = Carbon::parse($passwordReset->created_at);
        if (Carbon::now()->diffInMinutes($tokenCreatedAt) > 60) {
            DB::table('password_reset_tokens')
                ->where('token', $token)
                ->delete();
                
            return redirect()->route('front.password.request')
                ->with('error', 'Liên kết đặt lại mật khẩu đã hết hạn. Vui lòng yêu cầu liên kết mới.');
        }
        
        return view('frontend.auth.reset-password', ['token' => $token, 'email' => $passwordReset->email]);
    }
    
    /**
     * Xử lý đặt lại mật khẩu
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'token' => 'required'
        ], [
            'email.required' => 'Vui lòng nhập email của bạn',
            'email.email' => 'Địa chỉ email không hợp lệ',
            'email.exists' => 'Email này không tồn tại trong hệ thống',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới',
        ]);

        // Kiểm tra token có hợp lệ không
        $tokenData = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();
            
        if (!$tokenData) {
            return back()->withErrors(['error' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn!']);
        }

        // Cập nhật mật khẩu mới
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa token sau khi đặt lại mật khẩu thành công
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('front.login')
            ->with('success', 'Mật khẩu của bạn đã được đặt lại thành công! Bạn có thể đăng nhập với mật khẩu mới.');
    }
}

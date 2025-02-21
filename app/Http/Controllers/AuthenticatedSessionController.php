<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RecentBooksService; // Sửa namespace
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    protected $recentBooksService;

    public function __construct(RecentBooksService $recentBooksService)
    {
        $this->recentBooksService = $recentBooksService;
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function store(LoginRequest $request)
    {
        try {
            // Xác thực đăng nhập
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();

                // Merge sách vừa đọc từ cookie vào user
                $this->recentBooksService->mergeCookieBooksToUser(auth()->id());

                return redirect()->intended('/');
            }

            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ]);
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra khi đăng nhập.',
            ]);
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function destroy(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi đăng xuất.');
        }
    }
}
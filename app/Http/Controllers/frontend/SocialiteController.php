<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param Request $request
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request, string $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('front.login')
                ->with('error', 'Phương thức đăng nhập không được hỗ trợ');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle provider callback after authentication.
     *
     * @param Request $request
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, string $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('front.login')
                ->with('error', 'Phương thức đăng nhập không được hỗ trợ');
        }

        try {
            // Get user data from provider
            $socialUser = Socialite::driver($provider)->user();
            
            // Find user by provider ID
            $user = User::where($provider.'_id', $socialUser->getId())->first();
            
            // If user doesn't exist by provider ID, try to find by email
            if (!$user) {
                $user = User::where('email', $socialUser->getEmail())->first();
                
                // If user exists with email, update their social ID
                if ($user) {
                    $user->update([
                        $provider.'_id' => $socialUser->getId(),
                        'social_avatar' => $socialUser->getAvatar(),
                    ]);
                } else {
                    // Create new user
                    $user = User::create([
                        'full_name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'password' => Hash::make(Str::random(16)),
                        $provider.'_id' => $socialUser->getId(),
                        'social_avatar' => $socialUser->getAvatar(),
                        'phone' => '', // Required field in the model
                        'status' => 'active',
                        'role' => 'customer',
                    ]);
                    
                    // Generate user code
                    $user->code = "CUS" . sprintf('%09d', $user->id);
                    $user->save();
                    
                    // Fire registered event
                    event(new Registered($user));
                }
            }
            
            // Login user
            Auth::login($user, true);
            
            // Redirect to home page
            return redirect()->route('home')
                ->with('success', 'Đăng nhập thành công');
                
        } catch (\Exception $e) {
            return redirect()->route('front.login')
                ->with('error', 'Đăng nhập thất bại: ' . $e->getMessage());
        }
    }
} 
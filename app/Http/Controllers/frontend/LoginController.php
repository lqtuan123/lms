<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $this->middleware('guest')->except('logout');
    }
   
    public function viewLogin()
    {
        // dd(auth()->user());
        return view( 'frontend.auths.login' );
        
    }
    public function logout(Request $request)
    {
        // Log the user out of the application
        \Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate the session token to prevent session fixation attacks
        $request->session()->regenerateToken();

        // Redirect to the login page or home page
        return redirect('/');  // Or any other page you prefer
    }

    public function login(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
           
        ]);
       
        if(auth()->user())
        {
            return redirect()->route('/');
        }
        $olduser = \App\Models\User::where('phone',$request->email )->orWhere('email',$request->email )->first();
        if($olduser)
        {
            if (auth()->attempt(array('email' => $olduser->email, 'password' => $request->password))) {
                if (auth()->user()) {
                    if (isset($request->plink) && $request->plink!= '' )
                    {
                        return redirect($request->plink);
                    }
                    else
                        return redirect()->route('admin.home');
                }
            } else {
                return redirect()->route('/')
                    ->with('error', 'Email hoặc số điện thoại hoặc mật khẩu không đúng..');
            }
        }
        else {
            return redirect()->route('/')
                ->with('error', 'Email hoặc số điện thoại hoặc mật khẩu không đúng.');
        }
       
    }

    public function credentials(Request $request)
    {
        
        return ['email'=>$request->email,'password'=>$request->password, 'status'=>'active'];
    }
    public function viewAdminlogin()
    {
        return view('auth.admin.login');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Contracts\Auth\Factory as Auth;

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

    use AuthenticatesUsers;

    protected $auth;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->middleware('guest')->except('logout');
        $this->auth = $auth;
    }

    public function login()
    {
        // replace with AuthenticateUser@authorizeFirst
        return Socialite::with('reddit')
            ->with(['duration' => 'permanent'])
            ->scopes(['read'])
            ->redirect();
    }

    public function logout()
    {
        $this->auth->logout();

        return redirect('/');
    }

}

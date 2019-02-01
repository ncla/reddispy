<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginHandleRequest;
use App\Services\Auth\AuthenticateUser;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class RegisterController extends Controller implements LoginUserListener
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    public function callback(AuthenticateUser $authenticator, LoginHandleRequest $request)
    {
        /**
         * state	8CWcIZRyaiBT4TUnV5gTVQX6M5VE9oZDEOMPNBNq
        error	access_denied
         */

        return $authenticator->execute($this);
    }

    public function successfulLogin()
    {
        return redirect('/');
    }

    public function refusedLogin()
    {

    }

    public function failedLogin()
    {
        return redirect('/');
    }

}

<?php

namespace App\Services\Auth;

use App\Http\Controllers\Auth\LoginUserListener;
use App\Services\UserService;
use Illuminate\Contracts\Auth\Factory as Auth;
use Laravel\Socialite\Contracts\Factory as Socialite;

/**
 * Class AuthenticateUser
 *
 * https://laracasts.com/series/whats-new-in-laravel-5/episodes/9
 */
class AuthenticateUser
{
    /**
     * @var Socialite
     */
    private $socialite;
    private $userService;

    public function __construct(
        Socialite $socialiteProvider,
        UserService $userService,
        Auth $auth
    ) {
        $this->socialite = $socialiteProvider;
        $this->userService = $userService;
        $this->auth = $auth;
    }

    /**
     * @param LoginUserListener $listener
     * @return mixed
     * @throws \Exception
     */
    public function execute(LoginUserListener $listener)
    {
        $redditUser = $this->socialite->driver('reddit')->user();

        if ($this->validateAccessTokenResponseBody($redditUser->accessTokenResponseBody) === false) {
            return $listener->failedLogin();
        }

        $user = $this->userService->registerUserWithAccessToken(
            [
                'username' => $redditUser->getNickname(),
                'avatar' => strtok($redditUser->user['icon_img'], '?')
            ],
            [
                'refresh_token' => $redditUser->refreshToken,
                'access_token' => $redditUser->token,
                'token_expires_in_seconds' => $redditUser->expiresIn,
                'expires_at' => date('Y-m-d H:i:s', (time() + $redditUser->expiresIn))
            ]
        );

        $this->auth->login($user, true);

        return $listener->successfulLogin();
    }

    /**
     * TODO: Can this be moved somewhere else where?
     *
     * @param $data
     * @return bool
     */
    protected function validateAccessTokenResponseBody($data)
    {
        if (! isset($data['refresh_token'])) {
            return false;
        }

        $scopesArray = explode(' ', $data['scope']);
        $requiredScopes = RedditTokenConstants::$REQUIRED_SCOPES;

        /*
         * Compares both arrays to see if we have the same scope values
         * https://stackoverflow.com/a/32810261
         */
        if (
            ! ($scopesArray === array_intersect($scopesArray, $requiredScopes)
                && $requiredScopes === array_intersect($requiredScopes, $scopesArray))
        ) {
            return false;
        }

        return true;
    }
}

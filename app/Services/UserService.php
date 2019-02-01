<?php

namespace App\Services;

use App\Repositories\RedditTokenRepository;
use App\Repositories\UserRepository;

class UserService
{
    private $userRepository;
    private $tokenRepository;

    public function __construct(UserRepository $userRepository, RedditTokenRepository $tokenRepository)
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
    }

    // TO-DO: More specific Exception class
    public function registerUserWithAccessToken($userData, $tokenData)
    {
        $user = $this->userRepository->updateOrCreate(
            ['username' => $userData['username']],
            ['avatar' => $userData['avatar']]
        );

        if (!$user) {
            throw new \Exception('User failed to register in database');
        }

        // TODO: Why cant I just $tokenData here?
        $token = $this->tokenRepository->updateOrCreate(
            ['user_id' => $user->id],
            [
                'refresh_token' => $tokenData['refresh_token'],
                'access_token' => $tokenData['access_token'],
                'token_expires_in_seconds' => $tokenData['token_expires_in_seconds'],
                'expires_at' => $tokenData['expires_at']
            ]
        );

        if (!$token) {
            throw new \Exception('User tokens failed to register in database');
        }

        return $user;
    }

}
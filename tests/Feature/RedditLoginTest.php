<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestResponse;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Tests\TestCase;

class RedditLoginTest extends TestCase
{
    use RefreshDatabase;

    public function mockSocialiteFacade($username = 'Bob', $userOverrideProperties = [])
    {
        $user = factory(User::class)->make();

        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->nickname = $username;
        $socialiteUser->user = ['icon_img' => $user->avatar];
        $socialiteUser->token = '123123';
        $socialiteUser->refreshToken = '123123123';
        $socialiteUser->expiresIn = 3600;
        $socialiteUser->accessTokenResponseBody = [
            'refresh_token' => '123',
            'scope' => 'identity read'
        ];

        foreach($userOverrideProperties as $propertyName => $propertyValue) {
            $socialiteUser->{$propertyName} = $propertyValue;
        }

        $socialiteUser->expects($this->any())
            ->method('getNickname')
            ->willReturn($socialiteUser->nickname);

        $provider = $this->createMock(\SocialiteProviders\Reddit\Provider::class);
        $provider->expects($this->any())
            ->method('user')
            ->willReturn($socialiteUser);

        $stub = $this->createMock(Socialite::class);
        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        // Replace Socialite Instance with our mock
        $this->app->instance(Socialite::class, $stub);
    }

    public function testRedditProviderReturnsDataSuccessfullyAndCreatesNewUserAndToken()
    {
        $this->withoutExceptionHandling();

        $this->mockSocialiteFacade('bob');

        $response = $this->get('/auth/callback?state=123&code=123');

        $response->assertRedirect();
        $response->assertLocation('/');
        $this->assertDatabaseHas('users', ['username' => 'bob']);
        $this->assertDatabaseHas('tokens', ['user_id' => 1]);
    }

    public function testLoginRedirectsToRedditAuthorize()
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/auth/login');

        $response->assertRedirect();
        $this->assertContains('reddit.com', $response->getTargetUrl());
    }

    public function testHandleRedirectsToLoginIfCodeOrStateIsMissing()
    {
        $response = $this->get('/auth/callback');

        $response->assertSessionHasErrors();
        $response->assertRedirect();
    }

    public function testAuthenticatedUsersCannotLogInAgain()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
                         ->get('/auth/login');

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function testAuthenticatedUserLoggedOut()
    {
        $user = factory(User::class)->create();

        $this->be($user);

        $this->assertAuthenticated();

        $logoutResponse = $this->get('/auth/logout');

        $this->assertGuest();
        $logoutResponse->assertLocation('/');
    }

    public function testUnauthenticatedUserCanLogout()
    {
        $this->assertGuest();

        $logoutResponse = $this->get('/auth/logout');

        $logoutResponse->assertLocation('/');

        $this->assertGuest();
    }

    public function testInsufficientAccessScopeMustNotLoginAnUser()
    {
        $this->withoutExceptionHandling();

        $this->mockSocialiteFacade(
            'bob',
            [
                'accessTokenResponseBody' => [
                    'refresh_token' => '123',
                    'scope' => 'identity'
                ]
            ]
        );

        $response = $this->get('/auth/callback?state=123&code=123');

        $response->assertRedirect();
        $response->assertLocation('/');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'bob']);
    }

}

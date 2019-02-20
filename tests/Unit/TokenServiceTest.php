<?php

namespace Tests\Unit;

use App\Models\User\RedditToken;
use App\Repositories\Eloquent\EloquentTokenRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\TokenService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use DatabaseMigrations;

    public function testReturnOneTimeAccessToken()
    {
        $handlerStack = HandlerStack::create();

        $mockHandler = new MockHandler([
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                file_get_contents(base_path('/tests/_files/reddit_one_time_token_response.json'))
            )
        ]);

        $handlerStack->setHandler($mockHandler);
        $mockedClient = new Client([
            'handler' => $handlerStack
        ]);

        $service = new TokenService(new EloquentUserRepository(), new EloquentTokenRepository(), $mockedClient);

        $this->assertEquals(
            '-eTX94nBvasdfasdfasdfasdfk0h78s',
            $service->getOneTimeToken(Str::random(8), Str::random(8))
        );
    }

    public function testDatabaseTokenDoesReturnToken()
    {
        $service = new TokenService(new EloquentUserRepository(), new EloquentTokenRepository(), new Client());

        $tokenFactory = factory(RedditToken::class)->create([
            'expires_at' => now()->addMinutes(6)->toDateTimeString(),
        ]);

        $accessTokenFactory = $tokenFactory->access_token;

        $tokenFromDatabase = $service->getTokenFromDatabase();

        $this->assertEquals($accessTokenFactory, $tokenFromDatabase);
    }

    public function testNoDatabaseTokenGetsReturnedIfExpired()
    {
        $service = new TokenService(new EloquentUserRepository(), new EloquentTokenRepository(), new Client());

        $tokenFactory = factory(RedditToken::class)->create([
            'expires_at' => now()->addMinutes(4)->toDateTimeString(),
        ]);

        $tokenFromDatabase = $service->getTokenFromDatabase();

        $this->assertNull($tokenFromDatabase);
        $this->assertDatabaseHas('tokens', $tokenFactory->toArray());
    }
}

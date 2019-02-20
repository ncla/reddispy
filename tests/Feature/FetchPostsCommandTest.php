<?php

namespace Tests\Feature;

use App\Factories\RequestClientFactoryInterface;
use App\Models\Reddit\PostHistory;
use App\Models\User\RedditToken;
use App\Scraper\Reddit\RedditPostScraper;
use App\Scraper\Reddit\RequestManager\RedditPostsBulk;
use App\Services\TokenService;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\RequestManagerMockTrait;
use Tests\TestCase;
use Mockery as m;
use App\Models\Reddit\Post;

class FetchPostsCommandTest extends TestCase
{
    use RefreshDatabase;
    use RequestManagerMockTrait;

    protected $requestContainer = [];

    public function testRedditPostsScraperCompletes()
    {
        $mockHandler = new MockHandler([
            new Response(
                200,
                ['X-Foo' => 'Bar'],
                file_get_contents(base_path('/tests/_files/reddit_posts_bulk_api_response.json'))
            )
        ]);

        $mockedClient = $this->setUpRedditPostClientWithMockHandler($mockHandler);

        $factoryMock = m::mock(RequestClientFactoryInterface::class);
        $factoryMock->shouldReceive('getRedditRateLimitedClient')
            ->once()
            ->andReturn($mockedClient);

        $this->app->instance(RequestClientFactoryInterface::class, $factoryMock);

        $this->artisan('fetch:posts', [
            'subreddits' => ['test'],
            '--without-token' => true,
            '--pages' => 1
        ])->assertExitCode(0);

        $this->assertCount(50, Post::all());
        $this->assertCount(50, PostHistory::all());
    }

    public function testIfOneTimeAccessTokenIsUsed()
    {
        $scraperMock = m::mock(RedditPostScraper::class);
        $scraperMock
            ->shouldReceive('scrape')
            ->once();

        $scraperMock->requestManager = m::mock(RedditPostsBulk::class);
        $scraperMock->requestManager
            ->shouldReceive('setOption')
            ->times(3);

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

        $this->app->instance(RedditPostScraper::class, $scraperMock);

        // Ideally we should be doing contextual instance, but I did not find a way to do it at this time
        $this->app->instance(ClientInterface::class, $mockedClient);

        $this->artisan('fetch:posts', [
            'subreddits' => ['test'],
            '--without-token' => false,
            '--pages' => 1
        ])->assertExitCode(0)
            ->expectsOutput('No user oAuth tokens available, using dev API keys to acquire token')
            ->expectsOutput('One time token is null: false');
    }

    public function testIfDatabaseTokenIsUsed()
    {
        $scraperMock = m::mock(RedditPostScraper::class);
        $scraperMock
            ->shouldReceive('scrape')
            ->once();

        $scraperMock->requestManager = m::mock(RedditPostsBulk::class);
        $scraperMock->requestManager
            ->shouldReceive('setOption')
            ->times(3);

        $test = factory(RedditToken::class)->create([
            'expires_at' => now()->addMinutes(6)->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('tokens', $test->toArray());

        $this->app->instance(RedditPostScraper::class, $scraperMock);

        $this->artisan('fetch:posts', [
            'subreddits' => ['test'],
            '--without-token' => false,
            '--pages' => 1
        ])->assertExitCode(0)
            ->expectsOutput('User token is null: false');
    }
}

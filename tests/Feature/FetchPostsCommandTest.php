<?php

namespace Tests\Feature;

use App\Factories\RequestClientFactoryInterface;
use App\Models\Reddit\PostHistory;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

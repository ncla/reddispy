<?php

namespace Tests\Unit;

use App\Factories\RequestClientFactory;
use App\Factories\RequestClientFactoryInterface;
use App\Scraper\Reddit\RateLimiter\RedditRateLimitProvider;
use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager as BaseOneByOneRequestor;
use App\Scraper\Reddit\RequestManager\RedditPostsBulk as RedditPostRequestor;
use App\Scraper\Base\RateLimiter\GuzzleRateLimiter as RateLimiter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Tests\RequestManagerMockTrait;
use Tests\TestCase;

class RequestManagerTest extends TestCase
{
    use RequestManagerMockTrait;

    protected $requestFactory;

    protected $requestor;

    protected $requestContainer = [];

    public function testCreatesRedditPostRequestsAndAddsThemToArray()
    {
        $requestor = new RedditPostRequestor(new RequestClientFactory());
        $requestor->options(['subreddits' => ['Muse']]);

        $requests = $requestor->createRequests();

        $this->assertNotEmpty($requests);
    }

    public function testRedditPostsRequestHasReddiSpyUserAgent()
    {
        $requestor = new RedditPostRequestor(new RequestClientFactory());
        $requestor->options(['subreddits' => ['Muse']]);
        $requests = $requestor->createRequests();

        $this->assertStringContainsString('ReddiSpy', $requests[0]->getHeader('user-agent')[0]);
    }

    public function testRequestResponsesGetPushedToResponseArray()
    {
        $mockHandler = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['Muse' => true])),
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['Radiohead' => true])),
        ]);

        $mockedClient = $this->setUpRedditPostClientWithMockHandler($mockHandler);
        $factoryMock = $this->createMock(RequestClientFactoryInterface::class);
        $factoryMock->method('getRedditRateLimitedClient')
            ->willReturn($mockedClient);

        $requestor = new RedditPostRequestor($factoryMock);
        $requestor->options(['subreddits' => ['Muse', 'Radiohead']]);
        $requestor->scrape();

        $this->assertCount(2, $requestor->getAllResponses());
    }

    public function testPostResponsesThatReturnClientErrorStatusCodesThrowException()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(429, ['X-Foo' => 'Bar'], json_encode(['Muse' => true]))
        ]);

        $mockedClient = $this->setUpRedditPostClientWithMockHandler($mock);
        $factoryMock = $this->createMock(RequestClientFactoryInterface::class);
        $factoryMock->method('getRedditRateLimitedClient')
            ->willReturn($mockedClient);

        $requestor = new RedditPostRequestor($factoryMock);

        $requestor->options(['subreddits' => ['Muse']]);
        $requestor->scrape();
    }

    public function testPostResponsesThatReturnServerErrorStatusCodesSilentlyFail()
    {
        $mock = new MockHandler([
            new Response(500, ['X-Foo' => 'Bar'], json_encode(['Muse' => true]))
        ]);

        $mockedClient = $this->setUpRedditPostClientWithMockHandler($mock);
        $factoryMock = $this->createMock(RequestClientFactoryInterface::class);
        $factoryMock->method('getRedditRateLimitedClient')
            ->willReturn($mockedClient);

        $requestor = new RedditPostRequestor($factoryMock);

        $requestor->options(['subreddits' => ['Muse']]);
        $requestor->scrape();

        $this->assertEmpty($requestor->getAllResponses());
    }

    public function testRequestManagerShouldWaitBeforeSendingNextRequest()
    {
        $mock = new MockHandler([
            new Response(
                200,
                ['x-ratelimit-remaining' => 0, 'x-ratelimit-reset' => 1.5],
                json_encode(['Muse' => true])
            ),
            new Response(
                200,
                ['x-ratelimit-remaining' => 0, 'x-ratelimit-reset' => 4],
                json_encode(['Muse' => true])
            )
        ]);

        $mockedClient = $this->setUpRedditPostClientWithMockHandler($mock);
        $factoryMock = $this->createMock(RequestClientFactoryInterface::class);
        $factoryMock->method('getRedditRateLimitedClient')
            ->willReturn($mockedClient);

        $requestor = new RedditPostRequestor($factoryMock);
        $requestor->concurrency = 1;

        $requestor->options(['subreddits' => ['Muse', 'Radiohead']]);
        $requestor->setOption('pages_per_subreddit', 1);
        $requestor->scrape();

        $this->assertCount(2, $this->requestContainer);

        $firstRespTime = $this->requestContainer[0]['response']->_responseTime;
        $secondRespTime = $this->requestContainer[1]['response']->_responseTime;

        $this->assertGreaterThan(($secondRespTime - $firstRespTime), 2);
    }

}

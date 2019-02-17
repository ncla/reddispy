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
use Tests\TestCase;

class RequestManagerTest extends TestCase
{
    protected $requestFactory;

    protected $requestor;

    protected $requestContainer = [];

    protected function getGuzzleTimeMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) {
                        $response->_responseTime = microtime(true);

                        return $response;
                    }
                );
            };
        };
    }

    /**
     * @param MockHandler $mockHandler
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function setUpRedditPostClientWithMockHandler(MockHandler $mockHandler)
    {
        $handlerStack = HandlerStack::create();

        $history = Middleware::history($this->requestContainer);

        $handlerStack->push($history);
        $handlerStack->push(new RateLimiter(new RedditRateLimitProvider()));

        $handlerStack->push($this->getGuzzleTimeMiddleware());

        $handlerStack->setHandler($mockHandler);
        $client = new Client([
            'handler' => $handlerStack
        ]);

        $factoryMock = $this->createMock(RequestClientFactoryInterface::class);
        $factoryMock->method('getRedditRateLimitedClient')
            ->willReturn($client);

        return $factoryMock;
    }

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

        $factoryMock = $this->setUpRedditPostClientWithMockHandler($mockHandler);

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

        $factoryMock = $this->setUpRedditPostClientWithMockHandler($mock);
        $requestor = new RedditPostRequestor($factoryMock);

        $requestor->options(['subreddits' => ['Muse']]);
        $requestor->scrape();
    }

    public function testPostResponsesThatReturnServerErrorStatusCodesSilentlyFail()
    {
        $mock = new MockHandler([
            new Response(500, ['X-Foo' => 'Bar'], json_encode(['Muse' => true]))
        ]);

        $factoryMock = $this->setUpRedditPostClientWithMockHandler($mock);
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

        $container = [];

        $factoryMock = $this->setUpRedditPostClientWithMockHandler($mock, $container);
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

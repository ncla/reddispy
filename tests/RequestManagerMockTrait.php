<?php

namespace Tests;

use App\Scraper\Reddit\RateLimiter\RedditRateLimitProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use App\Scraper\Base\RateLimiter\GuzzleRateLimiter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait RequestManagerMockTrait
{
    /**
     * @return \Closure
     */
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
     * @return Client
     */
    protected function setUpRedditPostClientWithMockHandler(MockHandler $mockHandler)
    {
        $handlerStack = HandlerStack::create();

        $history = Middleware::history($this->requestContainer);

        $handlerStack->push($history);
        $handlerStack->push(new GuzzleRateLimiter(new RedditRateLimitProvider()));

        $handlerStack->push($this->getGuzzleTimeMiddleware());

        $handlerStack->setHandler($mockHandler);
        $mockedClient = new Client([
            'handler' => $handlerStack
        ]);

        return $mockedClient;
    }
}
<?php

namespace App\Factories;

use App\Scraper\Base\RateLimiter\GuzzleRateLimiter;
use App\Scraper\Reddit\RateLimiter\RedditRateLimitProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;

class RequestClientFactory implements RequestClientFactoryInterface
{
    public function getClient()
    {
        return new Client();
    }

    public function getRedditRateLimitedClient()
    {
        $rateLimitProvider = new RedditRateLimitProvider();

        $handlerStack = HandlerStack::create(new CurlMultiHandler());

        $handlerStack->push(new GuzzleRateLimiter($rateLimitProvider));

        return new Client(['handler' => $handlerStack]);
    }
}

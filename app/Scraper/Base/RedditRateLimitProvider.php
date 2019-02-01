<?php

namespace App\Scraper\Base;

use Concat\Http\Middleware\RateLimitProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Class RedditRateLimitProvider
 *
 * https://github.com/reddit-archive/reddit/wiki/API#rules
 * https://github.com/rtheunissen/guzzle-rate-limiter
 *
 * @package App\Scraper\Base
 */
class RedditRateLimitProvider implements RateLimitProvider
{
    public function getLastRequestTime(RequestInterface $request)
    {
        return Cache::get('last_request_time');
    }

    public function setLastRequestTime(RequestInterface $request)
    {
        Cache::put('last_request_time', microtime(true));
    }

    public function getRequestTime(RequestInterface $request)
    {
        return microtime(true);
    }

    public function getRequestAllowance(RequestInterface $request)
    {
        return Cache::get('request_allowance');
    }

    public function setRequestAllowance(ResponseInterface $response)
    {
        // Let's also assume that the response contains two headers:
        //     - ratelimit-remaining
        //     - ratelimit-window
        //
        // The first header tells us how many requests we have left in the
        // current window, the second tells us how many seconds are left in the
        // window before it expires.
        $requests = $response->getHeader('x-ratelimit-remaining');
        $seconds  = $response->getHeader('x-ratelimit-reset');

        if (empty($requests) || empty($seconds)) {
            Cache::put('request_allowance', (float) 1);
            return;
        }

        // The allowance is therefore how much time is remaining in our window
        // divided by the number of requests we can still make. This is the
        // value we need to store to determine if a future request should be
        // delayed or not.
        $allowance = (float) $seconds[0] / $requests[0];

        Cache::put('request_allowance', $allowance);
    }

}
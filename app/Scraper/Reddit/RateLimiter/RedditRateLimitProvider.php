<?php

namespace App\Scraper\Reddit\RateLimiter;

use App\Scraper\Base\RateLimiter\RateLimitProvider;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RedditRateLimitProvider
 *
 * https://github.com/reddit-archive/reddit/wiki/API#rules
 * https://github.com/rtheunissen/guzzle-rate-limiter
 */
class RedditRateLimitProvider implements RateLimitProvider
{
    /**
     * @param RequestInterface $request
     * @return float|mixed|null
     */
    public function getLastRequestTime(RequestInterface $request)
    {
        $lastRequestTime = Cache::get('last_request_time');

        return $lastRequestTime;
    }

    /**
     * @param RequestInterface $request
     * @param $delay float Time in seconds
     */
    public function setLastRequestTime(RequestInterface $request, $delay)
    {
        Cache::put('last_request_time', (microtime(true) + $delay), 10);
    }

    /**
     * @param RequestInterface $request
     * @return float|mixed
     */
    public function getRequestTime(RequestInterface $request)
    {
        return microtime(true);
    }

    /**
     * @param RequestInterface $request
     * @return float|mixed
     */
    public function getRequestAllowance(RequestInterface $request)
    {
        return Cache::get('request_allowance');
    }

    /**
     * Used to set the minimum amount of time that is required to pass between
     * this request and the next request in seconds.
     *
     * @param ResponseInterface $response The resolved response.
     */
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
        $resetsInSeconds = $response->getHeader('x-ratelimit-reset');

        if (empty($requests) || empty($resetsInSeconds)) {
            return $this->setAllowanceInStorage((float) 1);
        }

        $requests = (float) $requests[0] ?? 0;
        $resetsInSeconds = (float) $resetsInSeconds[0] ?? 0;

        if ($requests == 0) {
            return $this->setAllowanceInStorage((float) $resetsInSeconds);
        }

        if ($resetsInSeconds == 0) {
            return $this->setAllowanceInStorage((float) 0);
        }

        // The allowance is therefore how much time is remaining in our window
        // divided by the number of requests we can still make. This is the
        // value we need to store to determine if a future request should be
        // delayed or not.
        $allowance = (float) $resetsInSeconds / $requests;

        return $this->setAllowanceInStorage((float) $allowance);
    }

    /**
     * @param $allowance float Allowance set in seconds
     */
    protected function setAllowanceInStorage($allowance)
    {
        Cache::put('request_allowance', (float) $allowance, 10);
    }
}

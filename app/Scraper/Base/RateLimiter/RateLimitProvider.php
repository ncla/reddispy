<?php

namespace App\Scraper\Base\RateLimiter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An object which manages rate data for a rate limiter, which uses the data to
 * determine wait duration. Keeps track of:
 *
 *  - Time at which the last request was made
 *  - The allowed interval between the last and next request
 */
interface RateLimitProvider
{
    /**
     * Returns when the last request was made.
     *
     * @param RequestInterface $request
     * @return float|null When the last request was made.
     */
    public function getLastRequestTime(RequestInterface $request);

    /**
     * Used to set the current time as the last request time to be queried when
     * the next request is attempted.
     * @param RequestInterface $request
     * @param $delay float Time when request sends
     */
    public function setLastRequestTime(RequestInterface $request, $delay);

    /**
     * Returns what is considered the time when a given request is being made.
     *
     * @param RequestInterface $request The request being made.
     *
     * @return float Time when the given request is being made.
     */
    public function getRequestTime(RequestInterface $request);

    /**
     * Returns the minimum amount of time that is required to have passed since
     * the last request was made. This value is used to determine if the current
     * request should be delayed, based on when the last request was made.
     *
     * Returns the allowed time between the last request and the next, which
     * is used to determine if a request should be delayed and by how much.
     *
     * @param RequestInterface $request The pending request.
     *
     * @return float The minimum amount of time that is required to have passed
     *               since the last request was made (in seconds).
     */
    public function getRequestAllowance(RequestInterface $request);

    /**
     * Used to set the minimum amount of time that is required to pass between
     * this request and the next (in seconds).
     *
     * @param ResponseInterface $response The resolved response.
     */
    public function setRequestAllowance(ResponseInterface $response);
}

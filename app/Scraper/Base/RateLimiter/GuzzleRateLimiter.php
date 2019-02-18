<?php

namespace App\Scraper\Base\RateLimiter;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Guzzle middleware which delays requests if they exceed a rate allowance.
 *
 * Original code from here: https://github.com/rtheunissen/guzzle-rate-limiter
 * Modified to use Guzzles native 'delay' option instead of sleep() methods, allowing for Guzzle
 * handlers to handle delays instead of Middleware itself.
 */
class GuzzleRateLimiter
{
    /**
     * @var RateLimitProvider
     */
    protected $provider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string|callable Constant or callable that accepts a Response.
     */
    protected $logLevel;

    /**
     * Creates a callable middleware rate limiter.
     *
     * @param RateLimitProvider $provider A rate data provider.
     * @param LoggerInterface   $logger
     */
    public function __construct(
        RateLimitProvider $provider,
        LoggerInterface $logger = null
    ) {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * Delays and logs the request then sets the allowance for the next request.
     *
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, $options) use ($handler) {

            // Amount of time to delay the request by
            $delay = $this->getDelay($request);

            if ($delay > 0) {
                $options = $this->delay($delay, $options);
                $this->log($request, $delay);
            }

            // Sets the time when this request is being made,
            // which allows calculation of allowance later on.
            $this->provider->setLastRequestTime($request, $delay);

            // Set the allowance when the response was received
            return $handler($request, $options)->then($this->setAllowance());
        };
    }

    /**
     * Logs a request which is being delayed by a specified amount of time.
     *
     * @param RequestInterface $request The request being delayed.
     * @param float            $delay The amount of time that the request is delayed for.
     */
    protected function log(RequestInterface $request, $delay)
    {
        if (isset($this->logger)) {
            $level = $this->getLogLevel($request);
            $message = $this->getLogMessage($request, $delay);
            $context = compact('request', 'delay');

            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Formats a request and delay time as a log message.
     *
     * @param RequestInterface $request The request being logged.
     * @param float $delay The amount of time that the request is delayed for.
     *
     * @return string Log message
     */
    protected function getLogMessage(RequestInterface $request, $delay)
    {
        return vsprintf("[%s] %s %s was delayed by {$delay} seconds", [
            gmdate('d/M/Y:H:i:s O'),
            $request->getMethod(),
            $request->getUri()
        ]);
    }

    /**
     * Returns the default log level.
     *
     * @return string LogLevel
     */
    protected function getDefaultLogLevel()
    {
        return LogLevel::DEBUG;
    }

    /**
     * Sets the log level to use, which can be either a string or a callable
     * that accepts a response (which could be null). A log level could also
     * be null, which indicates that the default log level should be used.
     *
     * @param string|callable|null
     */
    public function setLogLevel($logLevel)
    {
        $this->logLevel = $logLevel;
    }

    /**
     * Returns a log level for a given request.
     *
     * @param RequestInterface $request The request being logged.
     * @return string LogLevel
     */
    protected function getLogLevel(RequestInterface $request)
    {
        if (! $this->logLevel) {
            return $this->getDefaultLogLevel();
        }

        if (is_callable($this->logLevel)) {
            return call_user_func($this->logLevel, $request);
        }

        return (string) $this->logLevel;
    }

    /**
     * Returns the delay duration for the given request (in seconds).
     *
     * @param RequestInterface $request Request to get the delay duration for.
     *
     * @return float The delay duration (in seconds).
     */
    protected function getDelay(RequestInterface $request)
    {
        $lastRequestTime = $this->provider->getLastRequestTime($request);
        $requestAllowance = $this->provider->getRequestAllowance($request);
        $requestTime = $this->provider->getRequestTime($request);

        $this->log($request, json_encode([$lastRequestTime,
            $requestAllowance,
            $requestTime,
            max(0, $requestAllowance - ($requestTime - $lastRequestTime)),
            ($requestTime - $lastRequestTime),
            $requestAllowance - ($requestTime - $lastRequestTime)
        ]));

        // If lastRequestTime is null or false, the max will be 0.
        return max(0, $requestAllowance - ($requestTime - $lastRequestTime));
    }

    /**
     * Delays the given request by an amount of seconds.
     *
     * @param float $seconds The amount of time (in seconds) to delay by.
     * @param array $options Request options passed from Guzzle middleware
     * @codeCoverageIgnore
     * @return array
     */
    protected function delay($seconds, $options)
    {
        $options['delay'] = $seconds * 1000;

        return $options;
    }

    /**
     * Returns a callable handler which allows the provider to set the request
     * allowance for the next request, using the current response.
     *
     * @return \Closure Handler to set request allowance on the rate provider.
     */
    protected function setAllowance()
    {
        return function (ResponseInterface $response) {
            $this->provider->setRequestAllowance($response);

            return $response;
        };
    }
}

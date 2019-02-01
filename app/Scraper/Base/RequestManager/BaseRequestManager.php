<?php

namespace App\Scraper\Base\RequestManager;

use App\Scraper\Base\HasOptions;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class BaseRequestManager
 *
 * Do: generate requests, request pool, get responses and return. This only does requests, and gets responses.
 * Do not: specific request management (rate limits, concurrency), checking if response is good or not
 *
 * @package App\Scraper\Reddit\RequestManager
 */

abstract class BaseRequestManager
{
    use HasOptions;

    protected $client;
    protected $requests = [];
    protected $responses = [];

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    abstract public function createRequests();

    abstract public function sendRequests();

    // Rename function e.g. createAndSendRequests
    public function scrape()
    {
        $this->createRequests();
        $this->sendRequests();
    }

    public function getAllResponses()
    {
        return $this->responses;
    }

    protected function addResponse(ResponseInterface $response)
    {
        $this->responses[] = $response;
    }

    protected function addRequest($request)
    {
        $this->requests[] = $request;
    }
}
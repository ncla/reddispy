<?php

namespace App\Scraper\Reddit\RequestManager;

use App\Factories\RequestClientFactoryInterface;
use App\Scraper\Base\RequestManager\PoolPromiseReactiveDynamic;
use App\Scraper\Reddit\UserAgentGenerator;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class RedditPostsBulk extends PoolPromiseReactiveDynamic
{
    use UserAgentGenerator;

    /**
     * Listings currently return maximum of 1000 items.
     *
     * @var array
     */
    protected $options = [
        'pages_per_subreddit' => 1,
        'max_items_per_request' => 100,
        'subreddits' => [],
        'token' => null
    ];

    protected $pagesSeenPerSubreddit = [];

    protected function getClient(RequestClientFactoryInterface $clientFactory)
    {
        return $clientFactory->getRedditRateLimitedClient();
    }

    public function createRequests()
    {
        foreach ($this->getOption('subreddits') as $subreddit) {
            $this->addRequest(
                $this->createRequest($subreddit)
            );
        }

        return $this->requests;
    }

    /**
     * https://www.reddit.com/dev/api/oauth#GET_new
     */
    public function createRequest($subreddit, $pagesSeen = 0, $after = null)
    {
        $urlParams = [
            'limit' => $this->getOption('max_items_per_request'),
            'count' => $pagesSeen * $this->getOption('max_items_per_request'),
            'after' => $after
        ];

        $request = new Request(
            'GET',
            "https://oauth.reddit.com/r/${subreddit}/new/.json?" . http_build_query($urlParams),
            [
                'Authorization' => 'bearer' . ' ' . $this->getOption('token'),
                'User-Agent' => $this->createUserAgent()
            ]
        );

        $request->_requestManagerData = [
            'subreddit' => $subreddit
        ];

        return $request;
    }

    /**
     * Parse, determine if new request is needed, add it.
     *
     * @param $response ResponseInterface
     */
    protected function onSuccessfulResponse(ResponseInterface $response)
    {
        $sub = $response->_requestManagerData['subreddit'];

        if (!isset($this->pagesSeenPerSubreddit[$sub])) {
            $this->pagesSeenPerSubreddit[$sub] = 1;
        } else {
            $this->pagesSeenPerSubreddit[$sub]++;
        }

        if ($this->pagesSeenPerSubreddit[$sub] === $this->getOption('pages_per_subreddit')) {
            return;
        }

        $jsonResponse = json_decode($response->getBody());
        $after = $jsonResponse->data->after ?? null;

        if (!is_string($after)) {
            return;
        }

        $this->addRequest(
            $this->createRequest(
                $sub,
                $this->pagesSeenPerSubreddit[$sub],
                $after
            )
        );
    }

    protected function onRejectedResponse($exception)
    {
        if ($exception instanceof \GuzzleHttp\Exception\ServerException) {
            Log::debug($exception);
        } else {
            throw $exception;
        }
    }
}
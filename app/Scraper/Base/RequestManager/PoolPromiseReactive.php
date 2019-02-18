<?php

namespace App\Scraper\Base\RequestManager;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PoolPromiseReactive
 *
 * Only good for predetermined requests in advance, not dynamic.
 */
abstract class PoolPromiseReactive extends BaseRequestManager
{
    protected $requestPool;

    public $concurrency = 1;

    protected $requestOptions = [];

    /**
     * We attach requestManagerData if we want to keep track of what the request was
     * initially when looking at Response (it can be identifiable data or the Request/Promise object itself)
     *
     * https://stackoverflow.com/questions/22649888/how-to-match-a-result-to-a-request-when-sending-multiple-requests
     * http://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests
     */
    protected function requestGenerator()
    {
        while ($request = array_pop($this->requests)) {
            yield function ($poolOptions) use ($request) {
                $optionsMerged = array_merge($this->requestOptions, $poolOptions);

                return $this->client->sendAsync($request, $optionsMerged)->then(function (GuzzleResponse $response) use ($request) {
                    // Extend Response/Request classes to include this property by default?
                    // PHPStorm complains about the field being declared dynamically.
                    $response->_requestManagerData = $request->_requestManagerData ?? [];

                    return $response;
                });
            };
        }
    }

    public function sendRequests()
    {
        $this->requestPool = new Pool($this->client, $this->requestGenerator(), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (Response $response) {
                $this->onSuccessfulResponse($response);
                $this->addResponse($response);
            },
            'rejected' => function ($reason) {
                $this->onRejectedResponse($reason);
            },
            'options' => [
                'on_stats' => function (TransferStats $stats) {
                    $statsStr = ($stats->hasResponse() ? $stats->getResponse()->getStatusCode() : 0) . ' | ' .
                        $stats->getTransferTime() . ' | ' . $stats->getEffectiveUri();

                    Log::debug($statsStr);
                }
            ]
        ]);

        $this->requestPool->promise()->wait();
    }

    protected function onSuccessfulResponse(ResponseInterface $response)
    {
    }

    protected function onRejectedResponse($exception)
    {
        throw new $exception;
    }
}

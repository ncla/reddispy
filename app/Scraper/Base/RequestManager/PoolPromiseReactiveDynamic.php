<?php

namespace App\Scraper\Base\RequestManager;

use App\Factories\RequestClientFactoryInterface;
use App\Scraper\Base\Iterators\ExpectingIterator;
use App\Scraper\Base\Iterators\MapIterator;
use ArrayIterator;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

abstract class PoolPromiseReactiveDynamic extends BaseRequestManager
{
    public $concurrency = 10;

    protected $requestIterator;

    /**
     * PoolPromiseReactiveDynamic constructor.
     * @param $clientFactory RequestClientFactoryInterface
     */
    public function __construct(RequestClientFactoryInterface $clientFactory)
    {
        parent::__construct($clientFactory);

        $this->requestIterator = new ArrayIterator();
    }

    /**
     * We attach requestManagerData if we want to keep track of what the request was
     * initially when looking at Response (it can be identifiable data or the Request/Promise object itself)
     *
     * https://stackoverflow.com/questions/22649888/how-to-match-a-result-to-a-request-when-sending-multiple-requests
     * http://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests
     *
     * We also here have a clusterfuck that is called Iterator hell, and why this class exists instead of just
     * PoolPromiseReactive. The usage of generators and Guzzle Pool doesn't allow us to process dynamically added
     * new requests when request count is lower than concurrency limit. You can read extensively about this issue
     * in GitHub issue I opened:
     *
     * https://github.com/guzzle/guzzle/issues/2260
     * https://github.com/alexeyshockov/guzzle-dynamic-pool
     * https://stackoverflow.com/questions/42754389/guzzle-pool-wait-for-requests/43525426#43525426
     */
    protected function requestGenerator()
    {
        return new ExpectingIterator(
            new MapIterator(
                $this->requestIterator,
                function (Request $request, ArrayIterator $array) {
                    return $this->client->sendAsync($request)
                        ->then(function (ResponseInterface $response) use ($request, $array) {
                            $response->_requestManagerData = $request->_requestManagerData ?? [];

                            return $response;
                        });
                }
            )
        );
    }

    protected function addRequest($request)
    {
        parent::addRequest($request);

        $this->requestIterator->append($request);
    }

    public function sendRequests()
    {
        $promise = \GuzzleHttp\Promise\each_limit($this->requestGenerator(), $this->concurrency, function ($response) {
            $this->onSuccessfulResponse($response);
            $this->addResponse($response);
        }, function ($reason) {
            $this->onRejectedResponse($reason);
        });

        $promise->wait();
    }

    protected function onSuccessfulResponse(ResponseInterface $response)
    {
    }

    protected function onRejectedResponse($exception)
    {
        throw new $exception;
    }
}

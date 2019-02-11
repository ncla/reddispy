<?php

namespace App\Scraper\Base\RequestManager;

use App\Factories\RequestClientFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class BaseOneByOneRequestManager extends BaseRequestManager
{
    public function createRequests()
    {
        return parent::createRequests();
    }

    public function sendRequests()
    {
        foreach ($this->requests as $request) {
            try {
                $response = $this->client->send($request);
                $this->addResponse($response);
            } catch (RequestException $e) {
                $this->handleRequestException($e);
            }
        }
    }

    /**
     * Silently fail requests with status code of less than 400
     * You are suppose to extend/overwrite this method for your desired outcome
     *
     * @param RequestException $exception
     */
    protected function handleRequestException(RequestException $exception)
    {
        $statusCode = $exception->getResponse()->getStatusCode();

        if ($statusCode >= 400) {
            throw $exception;
        }
    }

    /**
     * @param RequestClientFactoryInterface $clientFactory
     * @return ClientInterface
     */
    protected function getClient(RequestClientFactoryInterface $clientFactory)
    {
        return $clientFactory->getClient();
    }
}
<?php

namespace App\Services;

use App\Repositories\Contract\TokenRepository;
use App\Repositories\Contract\UserRepository;
use App\Scraper\Reddit\UserAgentGenerator;
use GuzzleHttp\ClientInterface;

class TokenService
{
    use UserAgentGenerator;

    private $userRepository;
    private $tokenRepository;
    private $requestClient;

    public function __construct(
        UserRepository $userRepository,
        TokenRepository $tokenRepository,
        ClientInterface $requestClient
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->requestClient = $requestClient;
    }

    public function getTokenFromDatabase()
    {
        return $this->tokenRepository->getFresh();
    }

    /**
     * Used to retrieve a token based on developer credentials.
     * Only use this for one time purposes (e.g. initial setup, trying it out, testing).
     * I am not sure if the information returned from API would be as accurate as requesting
     * on behalf of the actual user.
     *
     * @param string $clientId  Reddit Client ID
     * @param string $clientSecret Reddit Client secret
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOneTimeToken($clientId, $clientSecret)
    {
        $response = $this->requestClient->request(
            'POST',
            'https://www.reddit.com/api/v1/access_token',
            [
                'auth' => [$clientId, $clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
                'headers' => [
                    'User-Agent' => $this->createUserAgent()
                ]
            ]
        );

        $responseJson = json_decode($response->getBody());

        return $responseJson->access_token;
    }

}
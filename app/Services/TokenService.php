<?php

namespace App\Services;

use App\Repositories\RedditTokenRepository;
use App\Repositories\UserRepository;
use App\Scraper\Reddit\UserAgentGenerator;
use GuzzleHttp\ClientInterface;
use App\Models\User\RedditToken as Token;
use Illuminate\Support\Facades\DB;

class TokenService
{
    use UserAgentGenerator;

    private $userRepository;
    private $tokenRepository;
    private $requestClient;

    public function __construct(
        UserRepository $userRepository,
        RedditTokenRepository $tokenRepository,
        ClientInterface $requestClient
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->requestClient = $requestClient;
    }

    public function getTokenForRequestor()
    {
        $this->getTokenFromDatabase();
        // First try getting a freshest, most relevant token from DB (user hardcoded, then newest)
        // If nothing, we use Application Only OAuth as a last-resort
    }

    public function getTokenFromDatabase()
    {
        // TODO: REVAMP REPOSITORIES FUCK
        // TODO: Global settings, fetch specified user token (hardcoding in database)
        // TODO: Change query to be based on `token_expires_in_seconds` column

        $token = Token::
            select('access_token')
            ->where('expires_at', '>', DB::raw('DATE_ADD(NOW(), INTERVAL 5 MINUTE)'))
            ->limit(1)
            ->get()
            ->first();

        return $token !== null ? $token->access_token : null;
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
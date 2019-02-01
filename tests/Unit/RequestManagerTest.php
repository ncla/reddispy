<?php

namespace Tests\Unit;

use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager;
use Grpc\Server;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\HandlerStack;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Scraper\Reddit\RequestManager\RedditPostsBulk as RedditPostRequestor;
use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager as BaseOneByOneRequestor;

class RequestManagerTest extends TestCase
{
    public function testCreatesRedditPostRequestsAndAddsThemToArray()
    {
        $requestor = new RedditPostRequestor(new Client);
        $requestor->options(['subreddits' => ['Muse']]);
        $requests = $requestor->createRequests();

        $this->assertNotEmpty($requests);
    }

    public function testRedditPostsRequestHasReddiSpyUserAgent()
    {
        $requestor = new RedditPostRequestor(new Client);
        $requestor->options(['subreddits' => ['Muse']]);
        $requests = $requestor->createRequests();

        $this->assertStringContainsString('ReddiSpy', $requests[0]->getHeader('user-agent')[0]);
    }

    public function testIfOptionsMerge()
    {
        $requestor = new BaseOneByOneRequestor(new Client);

        $this->assertEmpty($requestor->getOptions());

        $requestor->options(['subreddits' => ['Muse']]);

        $this->assertNotEmpty($requestor->getOptions()['subreddits']);
        $this->assertContains('Muse', $requestor->getOptions()['subreddits']);

        $requestor->options(['subreddits' => ['Muse 2']]);

        $this->assertNotContains('Muse', $requestor->getOptions()['subreddits']);
    }

    public function testRequestResponsesGetPushedToResponseArray()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['Muse' => true])),
            new Response(200, ['X-Foo' => 'Bar'], json_encode(['Radiohead' => true])),
        ]);

        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler]);

        $requestor = new RedditPostRequestor($client);
        $requestor->options(['subreddits' => ['Muse', 'Radiohead']]);
        $requestor->scrape();

        $this->assertCount(2, $requestor->getAllResponses());
    }

    public function testPostResponsesThatReturnClientErrorStatusCodesThrowException()
    {
        $this->expectException(ClientException::class);

        $mock = new MockHandler([
            new Response(429, ['X-Foo' => 'Bar'], json_encode(['Muse' => true]))
        ]);

        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler]);

        $requestor = new RedditPostRequestor($client);
        $requestor->options(['subreddits' => ['Muse']]);
        $requestor->scrape();
    }

    public function testPostResponsesThatReturnServerErrorStatusCodesSilentlyFail()
    {
        $mock = new MockHandler([
            new Response(500, ['X-Foo' => 'Bar'], json_encode(['Muse' => true]))
        ]);

        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler]);

        $requestor = new RedditPostRequestor($client);
        $requestor->options(['subreddits' => ['Muse']]);
        $requestor->scrape();

        $this->assertEmpty($requestor->getAllResponses());
    }

}

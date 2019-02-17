<?php

namespace Tests\Unit;

use App\Factories\RequestClientFactory;
use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager as BaseOneByOneRequestor;
use Tests\TestCase;

class OptionsTraitTest extends TestCase
{

    public function testIfOptionsSet()
    {
        $requestor = new BaseOneByOneRequestor(new RequestClientFactory());

        $this->assertEmpty($requestor->getOptions());

        $requestor->setOption('subreddits', 'Muse');

        $this->assertEquals('Muse', $requestor->getOption('subreddits'));
        $this->assertArrayHasKey('subreddits', $requestor->getOptions());
    }

    public function testIfOptionsMerge()
    {
        $requestor = new BaseOneByOneRequestor(new RequestClientFactory());

        $this->assertEmpty($requestor->getOptions());

        $requestor->options(['subreddits' => ['Muse']]);

        $this->assertNotEmpty($requestor->getOptions()['subreddits']);
        $this->assertContains('Muse', $requestor->getOptions()['subreddits']);

        $requestor->options(['subreddits' => ['Muse 2']]);

        $this->assertNotContains('Muse', $requestor->getOptions()['subreddits']);
    }

}

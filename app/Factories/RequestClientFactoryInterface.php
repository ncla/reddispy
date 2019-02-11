<?php

namespace App\Factories;

interface RequestClientFactoryInterface
{
    public function getClient();

    public function getRedditRateLimitedClient();
}
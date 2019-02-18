<?php

namespace App\Providers;

use App\Factories\RequestClientFactory;
use App\Factories\RequestClientFactoryInterface;
use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager;
use App\Scraper\Base\RequestManager\BaseRequestManager;
use App\Scraper\Base\Storage\DatabaseStorageInterface;
use App\Scraper\Base\Storage\InsertModel;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromisorInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        ClientInterface::class => Client::class,
        RequestClientFactoryInterface::class => RequestClientFactory::class,
        BaseRequestManager::class => BaseOneByOneRequestManager::class,
        DatabaseStorageInterface::class => InsertModel::class,
        PromisorInterface::class => Pool::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}

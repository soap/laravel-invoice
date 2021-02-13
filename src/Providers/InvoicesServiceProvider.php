<?php

namespace Soap\Invoices\Providers;

use Illuminate\Support\ServiceProvider;
use Soap\Invoics\Services\BillService;
use Soap\Invoices\Interfaces\BillServiceInterface;
use Soap\Invoices\Interfaces\InvoiceServiceInterface;
use Soap\Invoices\Services\InvoiceService;

class InvoicesServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $sourceViewsPath = __DIR__ . '/../../resources/views';
        $this->loadViewsFrom($sourceViewsPath, 'invoice');

        $this->publishes([
            $sourceViewsPath => resource_path('views/vendor/invoice'),
        ], 'views');

        // Publish a config file
        $this->publishes([
            __DIR__ . '/../../config/invoice.php' => config_path('invoice.php'),
        ], 'config');

        // Publish migrations
         $this->publishes([
             __DIR__ . '/../../database/migrations/2017_06_17_163005_create_invoices_tables.php'
             => database_path('migrations/2017_06_17_163005_create_invoices_tables.php'),
         ], 'migrations');

        $this->app->bind(InvoiceServiceInterface::class, function ($app) {
            return new InvoiceService();
        });
        $this->app->bind(BillServiceInterface::class, function ($app) {
            return new BillService();
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/invoice.php', 'invoice');
    }
}

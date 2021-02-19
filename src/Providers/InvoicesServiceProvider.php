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
        $this->registerResources();

        $this->defineAssetPublishing();

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        
        $this->registerServices();
        
    }

    /**
     * Setup the configuration for Invoices.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/soap.invoices.php', 'soap.invoices');
    }

    protected function registerResources()
    {
        $sourceViewsPath = __DIR__ . '/../../resources/views';
        $this->loadViewsFrom($sourceViewsPath, 'invoice');
    }

    /**
     * Define the asset publishing configuration.
     *
     * @return void
     */
    protected function defineAssetPublishing()
    {
        $sourceViewsPath = __DIR__ . '/../../resources/views';

        $this->publishes([
            $sourceViewsPath => resource_path('views/vendor/invoices'),
        ], 'views');

        // Publish a config file
        $this->publishes([
            __DIR__ . '/../../config/soap.invoices.php' => config_path('soap.invoices.php'),
        ], 'config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations/2020_02_10_163005_create_invoices_tables.php'
            => database_path('migrations/2020_02_10_163005_create_invoices_tables.php'),
        ], 'migrations');
    }

    /**
     * Setup the resource publishing groups for Invoices.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            // Publishing the configuration file.
            $this->publishes([
                __DIR__ . '/../config/soap.invoices.php' => config_path('soap.invoices.php'),
            ], 'invoices.config');

            // Publishing the views.
            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/invoices'),
            ], 'invoices.views');

            // Publishing the translation files.
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/invoices'),
            ], 'invoices.translations');
        }
    }

    protected function registerServices()
    {
        $this->app->bind(InvoiceServiceInterface::class, function ($app) {
            return new InvoiceService();
        });
        
        $this->app->bind(BillServiceInterface::class, function ($app) {
            return new BillService();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['invoice'];
    }
}

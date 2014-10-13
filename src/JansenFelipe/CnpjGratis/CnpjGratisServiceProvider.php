<?php

namespace JansenFelipe\CnpjGratis;

use Illuminate\Support\ServiceProvider;

class CnpjGratisServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->package('JansenFelipe/cnpj-gratis');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('cnpj_gratis', function() {
            return new \JansenFelipe\CnpjGratis\CnpjGratis;
        });
    }

}

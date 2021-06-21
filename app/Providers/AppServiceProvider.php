<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\UserRepositoryInterface',
            'App\Repositories\UserRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\RiderRepositoryInterface',
            'App\Repositories\RiderRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\PartnerRepositoryInterface',
            'App\Repositories\PartnerRepository'
        );

        $this->app->bind(
            'App\Repositories\Interfaces\AdminRepositoryInterface',
            'App\Repositories\AdminRepository'
        );


    }
}

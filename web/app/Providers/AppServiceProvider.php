<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('decimalsinzero', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[\d]+(\.0+)?$/', $value);
        }, 'The :attribute must have decimals ending in .00');
    }
}

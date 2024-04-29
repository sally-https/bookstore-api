<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

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
        Validator::extend('custom_date_format', function ($attribute, $value, $parameters, $validator) {
            try {
                $date = Carbon::createFromFormat('D M d Y H:i:s \G\M\TP', $value);
                return $date !== false;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
}

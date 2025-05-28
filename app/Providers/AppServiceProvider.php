<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


    public function boot()
    {
        Carbon::setLocale('id'); // Set locale to Indonesian

        // Logging semua query SQL
        DB::listen(function ($query) {
            Log::info('[SQL]', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time . ' ms'
            ]);
        });
    }
}
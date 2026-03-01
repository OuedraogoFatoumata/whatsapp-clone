<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Fixe le problème de longueur de clé avec MySQL
        Schema::defaultStringLength(191);
    }

    public function register(): void
    {
        //
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Model\Promocode;
use App\Model\Material_thing;
use App\Model\Empty_prize;

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
        Relation::enforceMorphMap([
            'Промокод'=>\App\Model\Promocode::class,
            'Пустой приз' => \App\Model\Empty_prize::class,
            'Вещь' => \App\Model\Material_thing::class,
        ]);
    }
}

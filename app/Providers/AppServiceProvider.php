<?php

namespace App\Providers;

use App\Models\Attempt;
use App\Models\EmptyPrize;
use App\Models\MaterialThing;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Promocode;

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
            'promocode'=>Promocode::class,
            'empty_prize' => EmptyPrize::class,
            'material_thing' => MaterialThing::class,
            'attempt'=>Attempt::class,
            'user' => User::class,

        ]);
    }
}

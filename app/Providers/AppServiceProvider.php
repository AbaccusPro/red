<?php

namespace App\Providers;

use App\Setting;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Guard $auth)
    {
        if (env('APP_ENV', 'local') !== 'local') {
            DB::connection()->disableQueryLog();
        }

        if (Schema::hasTable('users')) {
            view()->composer('*', function ($view) use ($auth) {
                if ($auth->user()) {
                    $currentUser = $auth->user();
                    if ($currentUser->language != null) {
                        App::setLocale($currentUser->language);
                    }
                } elseif (Schema::hasTable('settings')) {
                    App::setLocale(Setting::get('language', 'en'));
                }
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

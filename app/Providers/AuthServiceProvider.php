<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\SuperAdmin;
use App\Policies\TeamPolicy;
use App\Services\Auth\SuperAdminAuthGuard;
use App\Extensions\SuperAdminUserProvider;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

          // add custom guard provider
          \Auth::provider('super_admin', function ($app, array $config) {
            return new SuperAdminUserProvider($app);
          });
       
          // add custom guard
          \Auth::extend('super_admin', function ($app, $name, array $config) {
            return new SuperAdminAuthGuard(\Auth::createUserProvider($config['provider']), $app->make('request'));
          });
    }
}

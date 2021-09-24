<?php

namespace App\Providers;

use App\Models\Team;
use App\Policies\TeamPolicy;
use App\Extensions\SuperAdminAuthGuard;
use App\Extensions\SuperAdminUserProvider;
use App\Extensions\InstructorAuthGuard;
use App\Extensions\InstructorUserProvider;

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
          \Auth::provider('superadmin', function ($app, array $config) {
            return new SuperAdminUserProvider($app);
          });
       
          // add custom guard
          \Auth::extend('superadmin', function ($app, $name, array $config) {
            return new SuperAdminAuthGuard(\Auth::createUserProvider($config['provider']), $app->make('request'));
          });


          // add custom guard provider
          \Auth::provider('instructor', function ($app, array $config) {
            return new InstructorUserProvider($app);
          });
       
          // add custom guard
          \Auth::extend('instructor', function ($app, $name, array $config) {
            return new InstructorAuthGuard(\Auth::createUserProvider($config['provider']), $app->make('request'));
          });
    }
}

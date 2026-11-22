<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define role-based gates for authorization
        Gate::define('isRole', function ($user, ...$roles) {
            return in_array($user->role->name, $roles);
        });

        // Define individual role gates for more specific authorization
        Gate::define('isAdmin', function ($user) {
            return $user->role->name === 'admin';
        });

        Gate::define('isDataClerk', function ($user) {
            return $user->role->name === 'data_clerk';
        });

        Gate::define('isMarriageTeller', function ($user) {
            return $user->role->name === 'marriage_teller';
        });

        Gate::define('isMarriageRegistrar', function ($user) {
            return $user->role->name === 'marriage_registrar';
        });

        Gate::define('isPublic', function ($user) {
            return $user->role->name === 'public';
        });
    }
}
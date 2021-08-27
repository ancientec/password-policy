<?php

namespace Ancientec\PasswordPolicy;
use Illuminate\Support\ServiceProvider;
use Ancientec\PasswordPolicy\PasswordPolicy;

class PasswordPolicyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PasswordPolicy::class);
    }
    public function boot()
    {
        //
    }
}
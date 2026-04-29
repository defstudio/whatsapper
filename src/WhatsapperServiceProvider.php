<?php

namespace DefStudio\Whatsapper;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WhatsapperServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name('whatsapper')
            ->hasConfigFile()
            ->hasRoutes();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Whatsapper::class, fn () => new Whatsapper);
    }
}

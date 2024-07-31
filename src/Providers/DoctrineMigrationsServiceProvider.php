<?php

declare(strict_types=1);

namespace LaravelDoctrine\Providers;

use Doctrine\Migrations\Configuration\EntityManager\EntityManagerLoader as EntityManagerLoaderContract;
use Doctrine\Migrations\Configuration\Migration\ConfigurationLoader as ConfigurationLoaderContract;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Console\Commands\Migrations\DiffCommand;
use LaravelDoctrine\Console\Commands\Migrations\MigrateCommand;

class DoctrineMigrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerConfigurationLoader();
        $this->registerDependencyFactory();

        $this->commands([
            DiffCommand::class,
            MigrateCommand::class,
        ]);
    }

    protected function registerConfigurationLoader(): void
    {
        $this->app->singleton(ConfigurationLoaderContract::class, function () {
            return new PhpFile(config_path('migrations.php'));
        });
    }

    protected function registerDependencyFactory(): void
    {
        $this->app->singleton(DependencyFactory::class, function () {
            return DependencyFactory::fromEntityManager(
                $this->app->make(ConfigurationLoaderContract::class),
                $this->app->make(EntityManagerLoaderContract::class),
            );
        });
    }
}

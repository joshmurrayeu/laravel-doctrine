<?php

declare(strict_types=1);

namespace LaravelDoctrine\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Console\Commands\DataFixtures\LoadCommand;
use LaravelDoctrine\Loaders\FixtureLoader;

class DoctrineDataFixturesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // only continue if on dev

        $this->registerFixtureLoader();

        $this->commands([
            LoadCommand::class,
        ]);
    }

    protected function registerFixtureLoader(): void
    {
        $this->app->bind(FixtureLoader::class, function () {
            return (new FixtureLoader())->setInstantiator(function ($fixtureClass) {
                return $this->app->make($fixtureClass);
            });
        });
    }
}

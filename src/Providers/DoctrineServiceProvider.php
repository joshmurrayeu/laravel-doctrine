<?php

declare(strict_types=1);

namespace LaravelDoctrine\Providers;

use Closure;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\MySQL\Driver as MySqlDriver;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver as PostgresDriver;
use Doctrine\DBAL\Driver\PDO\SQLite\Driver as SqliteDriver;
use Doctrine\Migrations\Configuration\EntityManager\EntityManagerLoader as EntityManagerLoaderContract;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;
use LaravelDoctrine\Console\Commands\ORM\CreateCommand;
use LaravelDoctrine\Console\Commands\ORM\DropCommand;
use LaravelDoctrine\Console\Commands\ORM\GenerateProxiesCommand;
use LaravelDoctrine\Console\Commands\ORM\RefreshDatabaseCommand;
use LaravelDoctrine\Loaders\EntityManagerLoader;
use LaravelDoctrine\NamingStrategies\UnderscorePluralNamingStrategy;
use LaravelDoctrine\Validation\DoctrinePresenceVerifier;

class DoctrineServiceProvider extends ServiceProvider
{
    protected array $config;
    protected array $filtersToEnable = [];

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config' => base_path('config'),
        ], 'laravel-doctrine-config');
    }

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->fetchDoctrineConfig();

        $this->registerConnection();
        $this->registerConfig();

        $this->registerEntityManager();
        $this->registerRepositories();

        $this->registerPresenceVerifier();

        $this->commands([
            CreateCommand::class,
            DropCommand::class,
            GenerateProxiesCommand::class,
            RefreshDatabaseCommand::class,
        ]);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function fetchDoctrineConfig(): void
    {
        /** @var Repository $repository */
        $repository = $this->app->make(Repository::class);

        $this->config = $repository->get('doctrine', []);
    }

    protected function registerConnection(): void
    {
        $this->app->singleton(Connection::class, function () {
            $manager = $this->config['manager'];

            $driver = match ($manager['driver']) {
                'pgsql' => new PostgresDriver(),
                'mysql' => new MySqlDriver(),
                'sqlite' => new SqliteDriver(),
            };

            if ($driver instanceof SqliteDriver) {
                $dbname = $manager['dbname'];

                if ($dbname === ':memory:') {
                    $manager['memory'] = true;
                } else {
                    $manager['path'] = $dbname;
                }
            }

            // Custom types go here..... for future reference.
            // $connection->getDatabasePlatform()->registerDoctrineTypeMapping($name, $name);

            $connection = new Connection($manager, $driver);

            $connection->setAutoCommit(true);

            return $connection;
        });
    }

    protected function registerConfig(): void
    {
        $this->app->singleton(Configuration::class, function () {
            $configuration = new Configuration();

            $proxies = $this->config['proxies'];
            $paths = $this->config['paths'];
            $filters = $this->config['filters'];

            $configuration->setMetadataDriverImpl(new AttributeDriver($paths));
            $configuration->setNamingStrategy($this->app->make(UnderscorePluralNamingStrategy::class));

            $configuration->setProxyDir($proxies['path']);
            $configuration->setProxyNamespace($proxies['namespace']);
            $configuration->setAutoGenerateProxyClasses((bool)$proxies['auto_generate']);

            $middlewares = $this->config['middlewares'] ?? [];

            if (!empty($middlewares)) {
                $middlewares = array_map(function (Closure $closure) {
                    return $closure($this->app);
                }, $middlewares);

                $middlewares = array_filter($middlewares);
            }

            $configuration->setMiddlewares($middlewares);

            foreach ($filters as $filterClassName) {
                $filterName = $filterClassName::NAME;
                $this->filtersToEnable[] = $filterName;

                $configuration->addFilter($filterClassName::NAME, $filterClassName);
            }

            return $configuration;
        });
    }

    protected function registerEntityManager(): void
    {
        $this->app->singleton(EntityManager::class, function () {
            $entityManager = new EntityManager(
                $this->app->make(Connection::class),
                $this->app->make(Configuration::class),
                $this->app->make(EventManager::class),
            );

            foreach ($this->filtersToEnable as $index => $filter) {
                $entityManager->getFilters()->enable($filter);

                $this->filtersToEnable[$index] = null;
            }

            return $entityManager;
        });

        $this->app->bind(EntityManagerInterface::class, EntityManager::class);

        $this->app->singleton(EntityManagerProvider::class, function () {
            return new SingleManagerProvider(
                $this->app->make(EntityManager::class)
            );
        });

        $this->app->singleton(EntityManagerLoaderContract::class, function () {
            return new EntityManagerLoader(
                $this->app->make(EntityManager::class)
            );
        });
    }

    protected function registerRepositories(): void
    {
        $this->app->resolving(EntityManagerInterface::class, function (EntityManagerInterface $entityManager) {
            $meta = $entityManager->getMetadataFactory();

            foreach ($meta->getAllMetadata() as $classMetadata) {
                if (($repositoryClassName = $classMetadata->customRepositoryClassName) && !empty($repositoryClassName)) {
                    $this->app->singleton(
                        $repositoryClassName,
                        function () use ($repositoryClassName, $entityManager, $classMetadata) {
                            return new $repositoryClassName($entityManager, $classMetadata);
                        }
                    );
                }
            }

            $repository = $this->app->make(Repository::class);
            $subscribers = $repository->get('doctrine.subscribers');

            $eventManager = $entityManager->getEventManager();
            $events = $eventManager->getAllListeners();

            foreach ($subscribers as $subscriber) {
                if ($this->checkIfSubscriberIsRegistered($events, $subscriber)) {
                    continue;
                }

                $a = $this->app->make($subscriber);

                $entityManager->getEventManager()->addEventSubscriber($a);
            }
        });
    }

    protected function checkIfSubscriberIsRegistered(array $events, string $subscriber): bool
    {
        $results = array_map(function (array $listeners) use ($subscriber) {
            $listeners = array_map(function (object $listener) {
                return get_class($listener);
            }, $listeners);

            return in_array($subscriber, array_flip($listeners));
        }, $events);

        if (empty($results)) {
            return false;
        }

        return array_any($results, fn(bool $value) => $value === true);
    }

    protected function registerPresenceVerifier(): void
    {
        $this->app->resolving(Factory::class, function (Factory $factory) {
            $factory->setPresenceVerifier(
                $this->app->make(DoctrinePresenceVerifier::class)
            );
        });
    }
}

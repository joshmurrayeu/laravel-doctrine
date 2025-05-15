<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\DataFixtures;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use LaravelDoctrine\Loaders\FixtureLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

class LoadCommand extends Command
{
    protected $signature = 'doctrine:fixtures:load {--purge : Purge the database before running fixtures }';

    protected $description = 'Load data fixtures to your database.';

    public function __construct(protected EntityManager $entityManager, protected FixtureLoader $fixtureLoader)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws BindingResolutionException
     */
    public function handle(): int
    {
        $this->fixtureLoader->loadFromDirectory(database_path('fixtures'));

        $purge = $this->option('purge');
        $executor = new ORMExecutor($this->entityManager);

        if ($purge === true) {
            $executor->setPurger(new ORMPurger($this->entityManager));
        }

        $executor->setLogger($this->laravel->make(LoggerInterface::class));

        // execute() requires $append to be true/false -> the opposite of $purge.
        $executor->execute($this->fixtureLoader->getFixtures(), !$purge);

        $this->output->success('Done');

        return BaseCommand::SUCCESS;
    }
}

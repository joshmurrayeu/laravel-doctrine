<?php

declare(strict_types=1);

namespace LaravelDoctrine\DataFixtures\Console\Commands;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use LaravelDoctrine\Loaders\FixtureLoader;
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
     */
    public function handle(): int
    {
        $this->fixtureLoader->loadFromDirectory(database_path('fixtures'));

        $purge = $this->option('purge');
        $executor = new ORMExecutor($this->entityManager);

        if ($purge === true) {
            $executor->setPurger(new ORMPurger($this->entityManager));
        }

        // execute() requires $append to be true/false -> the opposite of $purge.
        $executor->execute($this->fixtureLoader->getFixtures(), !$purge);

        $this->output->success('Done');

        return BaseCommand::SUCCESS;
    }
}

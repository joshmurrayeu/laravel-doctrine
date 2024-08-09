<?php

declare(strict_types=1);

namespace LaravelDoctrine\DataFixtures\Console\Commands;

use Doctrine\Common\DataFixtures\Executor\MultipleTransactionORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Illuminate\Console\Command;
use LaravelDoctrine\Loaders\FixtureLoader;
use Symfony\Component\Console\Command\Command as BaseCommand;

use function LaravelDoctrine\Console\Commands\DataFixtures\database_path;

class LoadCommand extends Command
{
    protected $signature = 'doctrine:fixtures:load';

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

        $executor = new MultipleTransactionORMExecutor($this->entityManager, new ORMPurger());
        $executor->execute($this->fixtureLoader->getFixtures());

        $this->output->success('Done');

        return BaseCommand::SUCCESS;
    }
}

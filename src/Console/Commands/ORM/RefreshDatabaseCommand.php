<?php

namespace LaravelDoctrine\Console\Commands\ORM;

use Illuminate\Console\Command;
use LaravelDoctrine\Console\Commands\DataFixtures\LoadCommand;
use LaravelDoctrine\Console\Commands\Migrations\MigrateCommand;
use Symfony\Component\Console\Command\Command as BaseCommand;

class RefreshDatabaseCommand extends Command
{
    protected $signature = 'doctrine:orm:refresh';

    public function handle(): int
    {
        $commands = $this->getCommands();
        $progressBar = $this->output->createProgressBar(count($commands));

        $progressBar->start();
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");

        foreach ($commands as $message => $command) {
            $progressBar->setMessage(' ➡   ' . $message);

            $command();

            $progressBar->advance();
        }

        $progressBar->setMessage('  <info>Database has been refreshed.</info>');
        $progressBar->finish();

        $this->newLine();

        return BaseCommand::SUCCESS;
    }

    protected function getCommands(): array
    {
        $args = ['--no-interaction' => true];

        return [
            'Dropping the database...' => fn() => $this->callSilent(
                DropCommand::class,
                $args + [
                    '--force' => true,
                    '--full-database' => true
                ]
            ),
            'Migrating the database...' => fn() => $this->callSilent(MigrateCommand::class, $args),
            'Loading the fixtures...' => fn() => $this->callSilent(LoadCommand::class, $args),
        ];
    }
}

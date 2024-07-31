<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\Migrations;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as DoctrineMigrateCommand;
use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'doctrine:migrations:migrate
                            {version=latest : The version FQCN or alias (first, prev, next, latest) to migrate to.}
                            {--configuration= : The path to a migrations configuration file. <comment>[default: any of migrations.{php,xml,json,yml,yaml}]</comment>}
                            {--em= : The name of the entity manager to use.}
                            {--conn= : The name of the connection to use.}
                            {--db-configuration= : The path to a database connection configuration file.}
                            {--write-sql= : The path to output the migration SQL file. Defaults to current working directory.}
                            {--dry-run= : Execute the migration as a dry run.}
                            {--query-time= : Time all the queries individually.}
                            {--allow-no-migration= : Do not throw an exception if no migration is available.}
                            {--all-or-nothing= : Wrap the entire migration in a transaction.}';

    protected $description = 'Execute a migration to a specified version or the latest available version.';

    /**
     * Execute the console command.
     */
    public function handle(DoctrineMigrateCommand $migrateCommand): int
    {
        $migrateCommand->initialize($this->input, $this->output);

        return $migrateCommand->execute($this->input, $this->output);
    }
}

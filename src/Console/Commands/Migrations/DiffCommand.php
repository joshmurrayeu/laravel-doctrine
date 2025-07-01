<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\Migrations;

use Doctrine\Migrations\Tools\Console\Command\DiffCommand as DoctrineDiffCommand;
use Illuminate\Console\Command;

class DiffCommand extends Command
{
    protected $signature = 'doctrine:migrations:diff
                            {--configuration= : The path to a migrations configuration file. <comment>[default: any of migrations.{php,xml,json,yml,yaml}]</comment>}
                            {--em= : The name of the entity manager to use.}
                            {--conn= : The name of the connection to use.}
                            {--db-configuration= : The path to a database connection configuration file.}
                            {--namespace= : The namespace to use for the migration (must be in the list of configured namespaces)}
                            {--filter-expression= : Tables which are filtered by Regular Expression.}
                            {--formatted : Format the generated SQL.}
                            {--line-length=120 : Max line length of unformatted lines.}
                            {--check-database-platform=false : Check Database Platform to the generated code.}
                            {--allow-empty-diff : Do not throw an exception when no changes are detected.}
                            {--nowdoc : Output the generated SQL as a nowdoc string (always active for formatted queries).}
                            {--from-empty-schema : Generate a full migration as if the current database was empty.}';
    protected $description = 'Generate a migration by comparing your current database to your mapping information.';

    /**
     * Execute the console command.
     */
    public function handle(DoctrineDiffCommand $diffCommand): int
    {
        $diffCommand->initialize($this->input, $this->output);

        return $diffCommand->execute($this->input, $this->output);
    }
}

<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\ORM;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand as DoctrineCreateCommand;
use Illuminate\Console\Command;

class CreateCommand extends Command
{
    protected $signature = 'doctrine:orm:create
                            {--em=default : Name of the entity manager to operate on.}
                            {--dump-sql : Instead of trying to apply generated SQLs into EntityManager Storage Connection, output them.}';

    protected $description = 'Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output';

    /**
     * Execute the console command.
     */
    public function handle(DoctrineCreateCommand $diffCommand): int
    {
        $diffCommand->initialize($this->input, $this->output);

        return $diffCommand->execute($this->input, $this->output);
    }
}

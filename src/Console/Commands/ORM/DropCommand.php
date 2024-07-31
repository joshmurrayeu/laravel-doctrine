<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\ORM;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand as DoctrineDropCommand;
use Illuminate\Console\Command;

class DropCommand extends Command
{
    protected $signature = 'doctrine:orm:drop
                            {--em=default : The name of the entity manager to use.}
                            {--dump-sql : The name of the connection to use.}
                            {--force : The name of the connection to use.}
                            {--full-database : The name of the connection to use.}';

    protected $description = 'Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output';

    /**
     * Execute the console command.
     */
    public function handle(DoctrineDropCommand $diffCommand): int
    {
        $diffCommand->initialize($this->input, $this->output);

        return $diffCommand->execute($this->input, $this->output);
    }
}

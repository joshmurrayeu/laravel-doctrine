<?php

declare(strict_types=1);

namespace LaravelDoctrine\Console\Commands\ORM;

use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand as DoctrineGenerateProxiesCommand;
use Illuminate\Console\Command;

class GenerateProxiesCommand extends Command
{
    protected $signature = 'doctrine:orm:proxies
                            {dest-path? : The path to generate your proxy classes. If none is provided, it will attempt to grab from configuration.}
                            {--em=default : Name of the entity manager to operate on}
                            {--filter=App : A string pattern used to match entities that should be processed.}';

    protected $description = 'Generates proxy classes for entity classes';

    /**
     * Execute the console command.
     */
    public function handle(DoctrineGenerateProxiesCommand $diffCommand): int
    {
        $diffCommand->initialize($this->input, $this->output);

        return $diffCommand->execute($this->input, $this->output);
    }
}

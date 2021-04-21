<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\Migrate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create/execute migrations')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new Migrate($input, $output, $this->getHelper('question')))();
    }
}

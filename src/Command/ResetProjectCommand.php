<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\ResetProject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetProjectCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Reset/fix non working project')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new ResetProject($input, $output, $this->getHelper('question')))();
    }
}

<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\CreateProject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProjectCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create enhavo project')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new CreateProject($input, $output, $this->getHelper('question')))();
    }
}
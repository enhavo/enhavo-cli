<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\CreateTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create phpunit test class')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new CreateTest($input, $output, $this->getHelper('question')))();
    }
}
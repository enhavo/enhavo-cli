<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\RecreateDatabase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecreateDatabaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Drop/create db')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = (new Factory())->create();
        return (new RecreateDatabase($input, $output, $this->getHelper('question'), $configuration))();
    }
}

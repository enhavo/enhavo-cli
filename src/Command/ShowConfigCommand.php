<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Task\ShowConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Show config file content')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $task = new ShowConfig($input, $output, $this->getHelper('question'));
        return $task();
    }
}
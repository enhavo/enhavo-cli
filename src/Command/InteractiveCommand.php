<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\Interactive;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InteractiveCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('always-use-default', 'a', InputOption::VALUE_NONE, 'Use default value for every question')
            ->setDescription('Interactive guide')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new Interactive($input, $output, $this->getHelper('question')))();
    }
}

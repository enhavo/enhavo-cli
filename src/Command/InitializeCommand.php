<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\Initialize;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command
{
    protected function configure()
    {
        $this
            ->addOption('always-use-default', 'a', InputOption::VALUE_NONE, 'Use default value for every question')
            ->setDescription('Initialize freshly installed project')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = (new Factory())->create();

        return (new Initialize($input, $output, $this->getHelper('question'), $configuration))();
    }
}

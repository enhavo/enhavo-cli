<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Task\EditConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Edit global config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new EditConfig($input, $output, $this->getHelper('question')))();
    }
}

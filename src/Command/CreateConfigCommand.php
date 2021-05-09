<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Task\CreateConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateConfigCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create global config file')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $task = new CreateConfig($input, $output, $this->getHelper('question'));
        $task->setAllowOverwrite(true);
        return $task();
    }
}

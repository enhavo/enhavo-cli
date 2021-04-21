<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\Update;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Update project after git pull')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new Update($input, $output, $this->getHelper('question')))();
    }
}

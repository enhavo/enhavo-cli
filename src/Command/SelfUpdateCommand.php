<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\SelfUpdate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Update enhavo-cli')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new SelfUpdate($input, $output, $this->getHelper('question')))();
    }
}

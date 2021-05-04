<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\InstallElasticSearch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallElasticSearchCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Install elastic search')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new InstallElasticSearch($input, $output, $this->getHelper('question')))();
    }
}

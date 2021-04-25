<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\NpmRelease;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NpmReleaseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Release npm package')
            ->addArgument('version', InputArgument::REQUIRED, 'Version to release')
            ->addOption('private', null, InputOption::VALUE_NONE, 'Private package')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $privateAccess = $input->getOption('private');
        $version = $input->getArgument('version');
        $configuration = (new Factory())->create();

        return (new NpmRelease($input, $output, $this->getHelper('question'), $configuration, $version, $privateAccess))();
    }
}

<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\VendorSymlink;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VendorSymlinkCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create or delete a symlink to a main repository')
            ->addArgument('package name', InputArgument::REQUIRED, 'package name e.g. enhavo/app-bundle')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $packageName = $input->getArgument('package name');
        return (new VendorSymlink($input, $output, $this->getHelper('question'), $packageName, (new Factory())->create(), new Factory()))();
    }
}

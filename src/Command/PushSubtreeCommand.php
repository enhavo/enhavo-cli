<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\PushSubtree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushSubtreeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Push subtrees')
            ->addOption('workspace', null, null, 'Workspace dir')
            ->addOption('remote', null, null, 'Remote name')
            ->addOption('branch', null, null, 'Branch name')
            ->addOption('force', null, null, 'Force push', false)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $workspace = $input->getOption('workspace');
        $remote = $input->getOption('remote');
        $branch = $input->getOption('branch');
        $force = $input->getOption('force');
        $configuration = (new Factory())->create();

        return (new PushSubtree($input, $output, $this->getHelper('question'), $configuration, $workspace, $remote, $branch, $force))();
    }
}
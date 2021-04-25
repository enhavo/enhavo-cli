<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\Subroutine\PushSubtree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PushSubtreeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Push subtrees')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Which name should be pushed')
            ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Tag')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED, 'Branch name')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force push')
            ->addOption('yes', null, InputOption::VALUE_NONE, 'Always yes')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        $force = $input->getOption('force');
        $branch = $input->getOption('branch');
        $tag = $input->getOption('tag');
        $yes = $input->getOption('yes');
        $configuration = (new Factory())->create();

        return (new PushSubtree($input, $output, $this->getHelper('question'), $configuration, $name, $force, $branch, $tag, $yes))();
    }
}

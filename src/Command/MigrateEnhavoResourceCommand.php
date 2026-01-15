<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Task\MigrateEnhavoResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateEnhavoResourceCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Migrate enhavo resources')
            ->addArgument('resource_file', InputArgument::REQUIRED, 'Path to resource file')
            ->addArgument('routes_dir', InputArgument::REQUIRED,  'Path to routes directory')
            ->addArgument('template_dir', InputArgument::OPTIONAL,  'Path to template directory')
            ->addOption('force', 'f', InputOption::VALUE_NONE,  'Force overwrite files')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new MigrateEnhavoResource($input, $output, $this->getHelper('question')))(getcwd());
    }
}

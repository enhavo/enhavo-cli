<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Symfony\Component\Console\Command\Command;

class ShowConfig extends AbstractSubroutine
{
    public function __invoke()
    {
        $path = $this->getPath();
        if ($path === null) {
            $this->output->writeln('No config file exits. Create a config file with enhavo create-config');
            return Command::SUCCESS;
        }

        $this->output->writeln(sprintf('<info>Config File: %s</info>', $path));

        $this->output->writeln('---');
        $this->output->writeln(sprintf('<fg=black;bg=cyan>%s</>', file_get_contents($path)));
        $this->output->writeln('---');
        return Command::SUCCESS;
    }

    protected function getPath()
    {
        $home = getenv("HOME");
        $dir = sprintf("%s/.enhavo", realpath($home));
        $path = sprintf("%s/config.yaml", $dir);
        if (!file_exists($path)) {
            return null;
        }
        return $path;
    }
}

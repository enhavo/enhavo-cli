<?php

namespace Enhavo\Component\Cli;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait BinConsoleTrait
{
    use ExecuteTrait;

    private function getConsolePath()
    {
        if (file_exists(sprintf('%/bin/console', getcwd()))) {
            return sprintf('%/bin/console', getcwd());
        } elseif (sprintf('%/app/console', getcwd())) {
            return sprintf('%/bin/console', getcwd());
        } else {
            throw new \RuntimeException('Can\'t find any symfony console command. Execute enhavo-cli inside your project');
        }
    }

    public function console(array $command, OutputInterface $output)
    {
        $path = $this->getConsolePath();

        $executeCommand = [$path];
        foreach ($command as $argument) {
            $executeCommand[] = $argument;
        }

        return $this->execute($executeCommand, $output);
    }

    public function existsConsoleCommand($command)
    {
        $path = $this->getConsolePath();

        $process = new Process([$path]);
        $process->run();

        $output = $process->getOutput();

        return strpos($output, $command) !== false;
    }
}
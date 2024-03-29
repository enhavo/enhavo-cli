<?php

namespace Enhavo\Component\Cli;

use Enhavo\Component\Cli\Exception\CommandFailException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait BinConsoleTrait
{
    use ExecuteTrait;

    private function getConsolePath()
    {
        $cwd = getcwd();
        if (file_exists(sprintf('%s/bin/console', $cwd))) {
            return sprintf('%s/bin/console', $cwd);
        } elseif (file_exists(sprintf('%s/app/console', $cwd))) {
            return sprintf('%s/app/console', $cwd);
        } else {
            throw new \RuntimeException('Can\'t find any symfony console command. Make sure enhavo-cli is executed within a symfony project folder.');
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

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput();
            if (empty($errorOutput)) {
                $errorOutput = $process->getOutput();
            }
            throw new CommandFailException($errorOutput);
        }

        $output = $process->getOutput();
        $errorOutput = $process->getErrorOutput();
        if ($process->getExitCode() !== 0) {
            throw new \Exception(sprintf('bin/console is not working due to: %s', $errorOutput));
        }

        return strpos($output, $command) !== false;
    }
}

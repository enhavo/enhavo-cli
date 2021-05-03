<?php

namespace Enhavo\Component\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

trait ExecuteTrait
{
    public function execute(array $command, OutputInterface $output)
    {
        $process = new Process($command);
        $process->setTimeout(0);
        $process->setTty(true);
        $process->start();

        $errorOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        foreach ($process as $type => $data) {
            if ($process::OUT === $type) {
                $output->writeln($data);
            } else {
                $errorOutput->writeln($data);
            }
        }

        return $process->isSuccessful() ? Command::SUCCESS : Command::FAILURE;
    }
}
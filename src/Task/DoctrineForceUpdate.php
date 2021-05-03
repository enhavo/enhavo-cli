<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

class DoctrineForceUpdate extends AbstractSubroutine
{
    use BinConsoleTrait;

    public function __invoke()
    {
        while(true) {
            $question = new Question('do force-update? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return Command::SUCCESS;
            } elseif (strtolower($option) === 'y') {
                return $this->console(['doctrine:schema:update', '--force'], $this->output);
            }
        }
    }
}
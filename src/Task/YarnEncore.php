<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\ExecuteTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

class YarnEncore extends AbstractSubroutine
{
    use ExecuteTrait;

    public function __invoke()
    {
        while(true) {
            $question = new Question('webpack encore? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return Command::SUCCESS;
            } elseif (strtolower($option) === 'y') {
                return $this->execute(['yarn', 'encore', 'dev', '--progress'], $this->output);
            }
        }
    }
}

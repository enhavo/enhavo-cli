<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

class CreateDatabase extends AbstractSubroutine
{
    use BinConsoleTrait;

    public function __invoke()
    {
        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'create database?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->console(['doctrine:database:create'], $this->output);
            }
        }
    }
}

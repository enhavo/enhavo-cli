<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;

class DropDatabase extends AbstractSubroutine
{
    use BinConsoleTrait;

    const COMMAND = 'doctrine:database:drop';

    public function __invoke()
    {
        if (!$this->existsConsoleCommand(self::COMMAND)) {
            return Command::SUCCESS;
        }

        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'drop database?', $this->defaultAnswer??self::ANSWER_NO);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->console([self::COMMAND, '--force'], $this->output);
            }
        }
    }
}

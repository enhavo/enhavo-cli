<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;

class EnhavoInit extends AbstractSubroutine
{
    use BinConsoleTrait;

    const COMMAND = 'enhavo:init';

    public function __invoke()
    {
        if (!$this->existsConsoleCommand(self::COMMAND)) {
            return Command::SUCCESS;
        }

        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'init enhavo?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->console([self::COMMAND], $this->output);
            }
        }
    }
}

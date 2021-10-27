<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;

class DoctrineForceUpdate extends AbstractSubroutine
{
    use BinConsoleTrait;

    const COMMAND = 'doctrine:schema:update';

    public function __invoke()
    {
        if (!$this->existsConsoleCommand(self::COMMAND)) {
            return Command::SUCCESS;
        }

        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'do force-update?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->console([self::COMMAND, '--force'], $this->output);
            }
        }
    }
}

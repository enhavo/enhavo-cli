<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Symfony\Component\Console\Command\Command;

class ExecuteMigrations extends AbstractSubroutine
{
    use BinConsoleTrait;

    public function __invoke()
    {
        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'execute migrations?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->console(['doctrine:migrations:migrate', '--no-interaction'], $this->output);
            }
        }
    }
}

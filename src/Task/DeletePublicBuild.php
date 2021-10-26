<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\ExecuteTrait;
use Symfony\Component\Console\Command\Command;

class DeletePublicBuild extends AbstractSubroutine
{
    use ExecuteTrait;

    public function __invoke()
    {
        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'delete public/build dir?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->execute(['rm', '-rf', 'public/build'], $this->output);
            }
        }
    }
}

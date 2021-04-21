<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\ComposerInstall;
use Symfony\Component\Console\Command\Command;

class Initialize extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        (new ComposerInstall( $this->input, $this->output, $this->questionHelper))();
        return Command::SUCCESS;
    }
}

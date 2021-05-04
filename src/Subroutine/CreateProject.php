<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Command\Command;

class CreateProject extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        return Command::SUCCESS;
    }
}
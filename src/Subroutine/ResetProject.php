<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\DeleteEnvLocal;
use Enhavo\Component\Cli\Task\DropDatabase;
use Symfony\Component\Console\Command\Command;

class ResetProject extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        $configuration = (new Factory())->create();

        (new DropDatabase($this->input, $this->output, $this->questionHelper))();
        (new DeleteEnvLocal($this->input, $this->output, $this->questionHelper))();
        (new DeleteAutogeneratedDirs($this->input, $this->output, $this->questionHelper))();

        (new Initialize($this->input, $this->output, $this->questionHelper, $configuration))();

        return Command::SUCCESS;
    }
}

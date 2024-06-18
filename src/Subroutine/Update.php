<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\ComposerInstall;
use Enhavo\Component\Cli\Task\DoctrineFixtures;
use Enhavo\Component\Cli\Task\DumpRoutes;
use Enhavo\Component\Cli\Task\EnhavoInit;
use Enhavo\Component\Cli\Task\ExecuteMigrations;
use Enhavo\Component\Cli\Task\YarnBundler;
use Enhavo\Component\Cli\Task\YarnInstall;
use Symfony\Component\Console\Command\Command;

class Update extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        (new ComposerInstall($this->input, $this->output, $this->questionHelper))();
        (new YarnInstall($this->input, $this->output, $this->questionHelper))();
        (new ExecuteMigrations($this->input, $this->output, $this->questionHelper))();
        (new DoctrineFixtures($this->input, $this->output, $this->questionHelper))();
        (new EnhavoInit($this->input, $this->output, $this->questionHelper))();
        (new YarnBundler($this->input, $this->output, $this->questionHelper))();
        (new DumpRoutes($this->input, $this->output, $this->questionHelper))();

        return Command::SUCCESS;
    }
}

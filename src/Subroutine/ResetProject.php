<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\DeleteEnvLocal;
use Enhavo\Component\Cli\Task\DeleteNodeModulesDir;
use Enhavo\Component\Cli\Task\DeleteVarCacheDir;
use Enhavo\Component\Cli\Task\DeleteVarMediaDir;
use Enhavo\Component\Cli\Task\DeleteVendorDir;
use Enhavo\Component\Cli\Task\DropDatabase;
use Symfony\Component\Console\Command\Command;

class ResetProject extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        $configuration = (new Factory())->create();

        (new DropDatabase($this->input, $this->output, $this->questionHelper))();
        // todo: add to clean-project-dir command?
        // todo: delete public/(build/bundles/js) directories?
        (new DeleteEnvLocal($this->input, $this->output, $this->questionHelper))();
        (new DeleteVendorDir($this->input, $this->output, $this->questionHelper))();
        (new DeleteNodeModulesDir($this->input, $this->output, $this->questionHelper))();
        (new DeleteVarCacheDir($this->input, $this->output, $this->questionHelper))();
        (new DeleteVarMediaDir($this->input, $this->output, $this->questionHelper))();

        (new Initialize($this->input, $this->output, $this->questionHelper, $configuration))();

        return Command::SUCCESS;
    }
}

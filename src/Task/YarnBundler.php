<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;

class YarnBundler extends AbstractSubroutine
{
    public function __invoke()
    {
        if ($this->isVite()) {
            return (new YarnBuild($this->input, $this->output, $this->questionHelper))();
        }

        return (new YarnEncore($this->input, $this->output, $this->questionHelper))();
    }

    private function isVite(): bool
    {
        if (getcwd() === false) {
            return false;
        }
        $path = sprintf('%s/node_modules/@vitejs', getcwd());
        return file_exists($path);
    }
}

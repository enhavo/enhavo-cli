<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\ExecuteTrait;

class EditConfig extends AbstractSubroutine
{
    use ExecuteTrait;

    public function __invoke()
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/config.yaml", realpath($home));
        return $this->execute(['vim', $path], $this->output);
    }
}

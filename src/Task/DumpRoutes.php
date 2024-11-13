<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\ExecuteTrait;
use Symfony\Component\Console\Command\Command;

class DumpRoutes extends AbstractSubroutine
{
    use ExecuteTrait;

    public function __invoke()
    {
        while($this->isFosRouting()) {
            $option = $this->askYesNo($this->input, $this->output, 'dump routes?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->execute(['yarn', 'routes:dump'], $this->output);
            }
        }
    }

    private function isFosRouting(): bool
    {
        if (getcwd() === false) {
            return false;
        }
        $path = sprintf('%s/vendor/friendsofsymfony/jsrouting-bundle', getcwd());
        return file_exists($path);
    }
}

<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\Process;

class ComposerInstall extends AbstractSubroutine
{
    public function __invoke()
    {
        while(true) {
            $question = new Question('composer install? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return;
            } elseif (strtolower($option) === 'y') {
                $process = new Process(['composer', 'install']);
                $process->start();
                $iterator = $process->getIterator($process::ITER_SKIP_ERR | $process::ITER_KEEP_OUTPUT);
                foreach ($iterator as $data) {
                    $this->output->writeln($data);
                }
                return;
            }
        }
    }
}

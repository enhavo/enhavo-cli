<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Enhavo\Component\Cli\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateMigrations extends AbstractSubroutine
{
    use BinConsoleTrait;

    public function __invoke()
    {
        while(true) {
            $question = new Question('create migrations? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return Command::SUCCESS;
            } elseif (strtolower($option) === 'y') {
                return $this->console(['doctrine:migrations:diff'], $this->output);
            }
        }
    }
}

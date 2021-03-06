<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

class Interactive extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        $subroutine = null;
        while ($subroutine === null) {
            $question = new Question('Choose a task:
[i] initialize freshly installed project
[u] update project after git pull
[r] reset/fix non working project
[d] drop/create db
[m] create/execute migrations
[l] create login
[c] cancel
type one of the options: ');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);
            $configuration = (new Factory())->create();
            if ($option == 'i') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'u') {
                $subroutine = new Update($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'r') {
                $subroutine = new ResetProject($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'd') {
                $subroutine = new RecreateDatabase($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'm') {
                $subroutine = new Migrate($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'l') {
                $subroutine = new CreateUser($this->input, $this->output, $this->questionHelper, $configuration);
            } elseif ($option == 'c') {
                $this->output->writeln('Abort');
                return Command::SUCCESS;
            } else {
                $this->output->write('Please choose valid command');
            }
        }

        if ($subroutine instanceof SubroutineInterface) {
            return $subroutine();
        }

        return Command::SUCCESS;
    }
}

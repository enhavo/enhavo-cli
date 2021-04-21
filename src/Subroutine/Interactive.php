<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

class Interactive extends AbstractSubroutine implements SubroutineInterface
{
    public function __invoke(): int
    {
        $subroutine = null;
        while($subroutine === null) {
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

            if ($option == 'i') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
            } elseif ($option == 'u') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
            } elseif ($option == 'r') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
            } elseif ($option == 'd') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
            } elseif ($option == 'm') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
            } elseif ($option == 'l') {
                $subroutine = new Initialize($this->input, $this->output, $this->questionHelper);
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

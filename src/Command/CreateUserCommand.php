<?php

namespace Enhavo\Component\Cli\Command;

use Enhavo\Component\Cli\Subroutine\CreateUser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Create user for login')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        return (new CreateUser($input, $output, $this->getHelper('question')))();
    }
}

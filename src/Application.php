<?php

namespace Enhavo\Component\Cli;

use Enhavo\Component\Cli\Command\InitializeCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application
{
    private $application;

    public function __construct()
    {
        $this->application = new BaseApplication();
    }

    public function run(InputInterface $input): int
    {
        $this->application->add(new InitializeCommand('init'));
        return $this->application->run($input);
    }
}

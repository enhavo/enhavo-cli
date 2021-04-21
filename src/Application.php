<?php

namespace Enhavo\Component\Cli;

use Enhavo\Component\Cli\Command\CreateUserCommand;
use Enhavo\Component\Cli\Command\InitializeCommand;
use Enhavo\Component\Cli\Command\InteractiveCommand;
use Enhavo\Component\Cli\Command\MigrateCommand;
use Enhavo\Component\Cli\Command\PushSubtreeCommand;
use Enhavo\Component\Cli\Command\RecreateDatabaseCommand;
use Enhavo\Component\Cli\Command\ResetProjectCommand;
use Enhavo\Component\Cli\Command\SelfUpdateCommand;
use Enhavo\Component\Cli\Command\UpdateCommand;
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
        $this->application->add(new CreateUserCommand('create-user'));
        $this->application->add(new InitializeCommand('initialize'));
        $this->application->add(new InteractiveCommand('interactive'));
        $this->application->add(new MigrateCommand('migrate'));
        $this->application->add(new RecreateDatabaseCommand('recreate-database'));
        $this->application->add(new ResetProjectCommand('reset-project'));
        $this->application->add(new UpdateCommand('update'));
        $this->application->add(new SelfUpdateCommand('self-update'));
        $this->application->add(new PushSubtreeCommand('push-subtree'));
        $this->application->setDefaultCommand('interactive');
        return $this->application->run($input);
    }
}

<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\ComposerInstall;
use Enhavo\Component\Cli\Task\CreateConfig;
use Enhavo\Component\Cli\Task\CreateDatabase;
use Enhavo\Component\Cli\Task\CreateEnv;
use Enhavo\Component\Cli\Task\CreateMigrations;
use Enhavo\Component\Cli\Task\DoctrineFixtures;
use Enhavo\Component\Cli\Task\DumpRoutes;
use Enhavo\Component\Cli\Task\EnhavoInit;
use Enhavo\Component\Cli\Task\ExecuteMigrations;
use Enhavo\Component\Cli\Task\YarnEncore;
use Enhavo\Component\Cli\Task\YarnInstall;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Initialize extends AbstractSubroutine implements SubroutineInterface
{
    /** @var Configuration */
    private $configuration;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, Configuration $configuration)
    {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
    }

    public function __invoke(): int
    {
        (new ComposerInstall($this->input, $this->output, $this->questionHelper))();
        (new YarnInstall($this->input, $this->output, $this->questionHelper))();
        (new CreateConfig($this->input, $this->output, $this->questionHelper))();
        (new CreateEnv($this->input, $this->output, $this->questionHelper, $this->configuration))();
        (new CreateDatabase($this->input, $this->output, $this->questionHelper))();
        // todo: import database?
        $createMigrationsTask = new CreateMigrations($this->input, $this->output, $this->questionHelper);
        ($createMigrationsTask->setDefaultAnswer(self::ANSWER_NO))();
        (new ExecuteMigrations($this->input, $this->output, $this->questionHelper))();
        (new DoctrineFixtures($this->input, $this->output, $this->questionHelper))();
        (new EnhavoInit($this->input, $this->output, $this->questionHelper))();
        (new \Enhavo\Component\Cli\Task\CreateUser($this->input, $this->output, $this->questionHelper, $this->configuration))();
        (new YarnEncore($this->input, $this->output, $this->questionHelper))();
        (new DumpRoutes($this->input, $this->output, $this->questionHelper))();

        return Command::SUCCESS;
    }
}

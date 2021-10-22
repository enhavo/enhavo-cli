<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\CreateEnv;
use Enhavo\Component\Cli\Task\CreateMigrations;
use Enhavo\Component\Cli\Task\DoctrineForceUpdate;
use Enhavo\Component\Cli\Task\ExecuteMigrations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Migrate extends AbstractSubroutine implements SubroutineInterface
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
        (new CreateEnv( $this->input, $this->output, $this->questionHelper, $this->configuration))();
        $create = (new CreateMigrations($this->input, $this->output, $this->questionHelper))();
        $execute = (new ExecuteMigrations($this->input, $this->output, $this->questionHelper))();
        if ($create === Command::SUCCESS && $execute === Command::SUCCESS) {
            (new DoctrineForceUpdate($this->input, $this->output, $this->questionHelper))();
        }

        return Command::SUCCESS;
    }
}

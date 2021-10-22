<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\SubroutineInterface;
use Enhavo\Component\Cli\Task\CreateDatabase;
use Enhavo\Component\Cli\Task\CreateEnv;
use Enhavo\Component\Cli\Task\CreateMigrations;
use Enhavo\Component\Cli\Task\DropDatabase;
use Enhavo\Component\Cli\Task\ExecuteMigrations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecreateDatabase extends AbstractSubroutine implements SubroutineInterface
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
        (new DropDatabase($this->input, $this->output, $this->questionHelper))();
        (new CreateDatabase($this->input, $this->output, $this->questionHelper))();

        return Command::SUCCESS;
    }
}

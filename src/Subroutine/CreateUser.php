<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends AbstractSubroutine implements SubroutineInterface
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
        $userTask = new \Enhavo\Component\Cli\Task\CreateUser($this->input, $this->output, $this->questionHelper, $this->configuration);
        $userTask->setAsk(false);
        return $userTask();
    }
}

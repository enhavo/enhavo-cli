<?php

namespace Enhavo\Component\Cli;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractSubroutine
{
    const ANSWER_YES = 'y';
    const ANSWER_NO = 'n';

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    /** @var QuestionHelper */
    protected $questionHelper;

    /** @var ?string */
    protected $defaultAnswer;

    /**
     * Interactive constructor.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     */
    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $this->input = $input;
        $this->output = $output;
        $this->questionHelper = $questionHelper;
    }

    protected function askYesNo(InputInterface $input, OutputInterface $output, string $text, string $default)
    {
        $question = new Question(sprintf('%s [%s/%s](%s): ', $text, self::ANSWER_YES, self::ANSWER_NO, $default), $default);
        return $this->isAlwaysUseDefault($input) ? $default : $this->questionHelper->ask($input, $output, $question);
    }

    protected function isAlwaysUseDefault(InputInterface $input): bool
    {
        if ($input->hasOption('always-use-default')) {
            return $input->getOption('always-use-default');
        }

        return false;
    }

    /**
     * @param string|null $defaultAnswer
     * @return $this
     */
    public function setDefaultAnswer(?string $defaultAnswer): AbstractSubroutine
    {
        $this->defaultAnswer = $defaultAnswer;

        return $this;
    }


}

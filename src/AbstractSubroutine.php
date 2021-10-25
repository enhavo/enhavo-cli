<?php

namespace Enhavo\Component\Cli;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use vendor\project\StatusTest;

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
        return $this->isAlwaysUseDefault() ? $default : $this->questionHelper->ask($input, $output, $question);
    }

    protected function isAlwaysUseDefault(): bool
    {
        return $this->input->getOption('always-use-default');
    }
}

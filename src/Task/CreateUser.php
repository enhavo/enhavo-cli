<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Enhavo\Component\Cli\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUser extends AbstractSubroutine
{
    use BinConsoleTrait;

    /** @var Configuration */
    private $configuration;

    /** @var boolean */
    private $ask = true;

    /** @var bool */
    private $askDefault = false;

    /** @var bool */
    private $useDefault = true;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, Configuration $configuration)
    {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
    }

    /**
     * @param bool $ask
     */
    public function setAsk(bool $ask): void
    {
        $this->ask = $ask;
    }

    /**
     * @param bool $askDefault
     */
    public function setAskDefault(bool $askDefault): void
    {
        $this->askDefault = $askDefault;
    }

    /**
     * @param bool $useDefault
     */
    public function setUseDefault(bool $useDefault): void
    {
        $this->useDefault = $useDefault;
    }


    public function __invoke()
    {
        if (!$this->ask) {
            return $this->createUser();
        }

        while(true) {
            $question = new Question('create user? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return Command::SUCCESS;
            } elseif (strtolower($option) === 'y') {
                return $this->createUser();
            }
        }
    }

    private function createUser()
    {
        if ($this->existsConsoleCommand('enhavo:user:create')) {
            $parameters = [];
            if ($this->configuration->getDefaultUserEmail() && $this->configuration->getDefaultUserPassword() && $this->useDefaults()) {
                $parameters[] = $this->configuration->getDefaultUserEmail();
                $parameters[] = $this->configuration->getDefaultUserPassword();
            }
            $parameters[] = '--super-admin';
            return $this->console(array_merge(['enhavo:user:create'], $parameters), $this->output);
        } else {
            $parameters = [];
            if ($this->configuration->getDefaultUserEmail() && $this->configuration->getDefaultUserPassword() && $this->useDefaults()) {
                $parameters[] = $this->configuration->getDefaultUserEmail();
                $parameters[] = $this->configuration->getDefaultUserEmail();
                $parameters[] = $this->configuration->getDefaultUserPassword();
            }
            $parameters[] = '--super-admin';

            return $this->console(array_merge(['fos:user:create'], $parameters), $this->output);
        }
    }

    private function useDefaults()
    {
        if (!$this->useDefault) {
            return false;
        }

        if ($this->askDefault) {
            while(true) {
                $question = new Question('use defaults? [y/n]', 'y');
                $option = $this->questionHelper->ask($this->input, $this->output, $question);
                if (strtolower($option) === 'n') {
                    return false;
                } elseif (strtolower($option) === 'y') {
                    return true;
                }
            }
        }

        return true;
    }
}

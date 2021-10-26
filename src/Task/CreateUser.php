<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\BinConsoleTrait;
use Enhavo\Component\Cli\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            $option = $this->askYesNo($this->input, $this->output, 'create user?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->createUser();
            }
        }
    }

    private function createUser()
    {
        $defaults = $this->useDefaults();
        if ($this->existsConsoleCommand('enhavo:user:create')) {
            $parameters = [];
            if ($this->configuration->getDefaultUserEmail() && $defaults) {
                $parameters[] = $this->configuration->getDefaultUserEmail();
            }
            if (count($parameters) === 1 && $this->configuration->getDefaultUserPassword() && $defaults) {
                $parameters[] = $this->configuration->getDefaultUserPassword();
            }
            $parameters[] = '--super-admin';
            return $this->console(array_merge(['enhavo:user:create'], $parameters), $this->output);
        } else {
            $parameters = [];
            if ($this->configuration->getDefaultUserEmail() && $this->useDefaults() && $defaults) {
                $parameters[] = $this->configuration->getDefaultUserEmail();
                $parameters[] = $this->configuration->getDefaultUserEmail();
            }
            if (count($parameters) === 2 && $this->configuration->getDefaultUserEmail() && $this->configuration->getDefaultUserPassword() && $defaults) {
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
                $option = $this->askYesNo($this->input, $this->output, 'use defaults?', self::ANSWER_YES);
                if (strtolower($option) === self::ANSWER_NO) {
                    return false;
                } elseif (strtolower($option) === self::ANSWER_YES) {
                    return true;
                }
            }
        }

        return true;
    }
}

<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class CreateEnv extends AbstractSubroutine
{
    /** @var Configuration */
    private $configuration;

    /** @var Filesystem */
    private $fs;

    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, Configuration $configuration)
    {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
        $this->fs = new Filesystem();
    }

    public function __invoke()
    {
        if ($this->fs->exists($this->getFile('.env.local'))) {
            return Command::SUCCESS;
        }

        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'create .env.local?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->createEnvFile($this->getFile('.env'), $this->getFile('.env.local'));
            }
        }
    }

    private function createEnvFile($original, $target)
    {
        $values = [];
        $originalKeys = $this->getKeysFromEnvFile($original);

        foreach ($originalKeys as $key) {
            if (($key === 'DATABASE_URL' || $key === 'DATABASE_DSN') && $this->configuration->getDefaultDatabaseHost()) {
                $values[$key] = $this->createDatabaseValue();
            } else {
                foreach ($this->configuration->getDefaultEnv() as $env) {
                    if ($env->getKey() === $key) {
                        $values[$key] = $env->getValue();
                    }
                }
            }
        }

        try {
            $this->writeFile($target, $values);
        } catch (\Exception $ex) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function createDatabaseValue()
    {
        while(true) {
            $question = new Question('what is the database name?');
            $database = $this->questionHelper->ask($this->input, $this->output, $question);

            if (!empty($database)) {
                return sprintf(
                    'mysql://%s:%s@%s:%s/%s',
                    $this->configuration->getDefaultDatabaseUser(),
                    $this->configuration->getDefaultDatabasePassword(),
                    $this->configuration->getDefaultDatabaseHost(),
                    $this->configuration->getDefaultDatabasePort(),
                    $database
                );
            }
        }
    }

    private function getKeysFromEnvFile($file)
    {
        $keys = [];
        $lines = file($file);
        foreach ($lines as $line) {
            $line = trim($line);
            if(preg_match('/^([A-Za-z_0-9]+)=.*/', $line, $matches)) {
                $keys[] = $matches[1];
            }
        }
        return $keys;
    }

    private function writeFile($file, $values)
    {
        $content = '';
        foreach ($values as $key => $value) {
            $content .= sprintf('%s="%s"%s', $key, $value, "\n");
        }
        $this->fs->dumpFile($file, $content);
    }

    protected function getFile($file)
    {
        return sprintf('%s/%s', getcwd(), $file);
    }
}

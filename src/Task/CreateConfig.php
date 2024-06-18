<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class CreateConfig extends AbstractSubroutine
{
    /** @var bool */
    private $allowOverwrite = false;

    public function __invoke()
    {
        if ($this->configExists()) {
            if (!$this->allowOverwrite) {
                return Command::SUCCESS;
            }
            $option = $this->askYesNo($this->input, $this->output, 'file "~/.enhavo/config.yaml" exists, edit?', self::ANSWER_NO);
            if ($option !== self::ANSWER_YES) {
                return Command::SUCCESS;
            }
            $configuration = (new Factory())->create();
            return $this->createConfigFile($configuration);
        }

        while(true) {
            $option = $this->askYesNo($this->input, $this->output, 'create config under "~/.enhavo/config.yaml"?', self::ANSWER_YES);

            if (strtolower($option) === self::ANSWER_NO) {
                return Command::SUCCESS;
            } elseif (strtolower($option) === self::ANSWER_YES) {
                return $this->createConfigFile();
            }
        }
    }

    private function createConfigFile(?Configuration $configuration = null)
    {
        $defaults = [
            'env' => []
        ];

        // user
        if ($value = $this->askQuestion('default user email %s?', $configuration?$configuration->getDefaultUserEmail():null)) {
            $defaults['user_email'] = $value;
        }

        if ($value = $this->askQuestion('default user password %s?', $configuration?$configuration->getDefaultUserPassword():null)) {
            $defaults['user_password'] = $value;
        }

        // database
        if ($value = $this->askQuestion('database user %s?', $configuration?$configuration->getDefaultDatabaseUser():'root')) {
            $defaults['database_user'] = $value;
        }

        if ($value = $this->askQuestion('database password %s?', $configuration?$configuration->getDefaultDatabasePassword():'root')) {
            $defaults['database_password'] = $value;
        }

        if ($value = $this->askQuestion('database host %s?', $configuration?$configuration->getDefaultDatabaseHost():'localhost')) {
            $defaults['database_host'] = $value;
        }

        if ($value = $this->askQuestion('database port %s?', $configuration?$configuration->getDefaultDatabasePort():3306)) {
            $defaults['database_port'] = intval($value);
        }

        // mailer
        if ($value = $this->askQuestion('MAILER_DSN %s?', $this->findEnv('MAILER_DSN', $configuration))) {
            $defaults['env']['MAILER_DSN'] = $value;
        }

        if ($value = $this->askQuestion('MAILER_FROM %s?', $this->findEnv('MAILER_FROM', $configuration))) {
            $defaults['env']['MAILER_FROM'] = $value;
        }

        if ($value = $this->askQuestion('MAILER_TO %s?', $this->findEnv('MAILER_TO', $configuration))) {
            $defaults['env']['MAILER_TO'] = $value;
        }

        if ($value = $this->askQuestion('MAILER_DELIVERY_ADDRESS %s?', $this->findEnv('MAILER_DELIVERY_ADDRESS', $configuration)??$defaults['env']['MAILER_TO'])) {
            $defaults['env']['MAILER_DELIVERY_ADDRESS'] = $value;
        }

        // env
        if ($value = $this->askQuestion('APP_ENV %s?', $this->findEnv('APP_ENV', $configuration)??'dev')) {
            $defaults['env']['APP_ENV'] = $value;
        }
        // debug
        if ($value = $this->askQuestion('APP_ENV %s?', false)) {
            $defaults['env']['APP_DEBUG'] = $value;
        }
        $content = Yaml::dump(['defaults' => $defaults], 3, 4);

        $this->writeFile($content);

        return Command::SUCCESS;
    }

    protected function findEnv($key, ?Configuration $configuration)
    {
        $env = $configuration ? $configuration->getDefaultEnv() : [];

        foreach ($env as $item) {
            if ($item->getKey() === $key) {
                return $item->getValue();
            }
        }

        return null;
    }

    protected function writeFile($content)
    {
        $home = getenv("HOME");
        $dir = sprintf("%s/.enhavo", realpath($home));
        $path = sprintf("%s/config.yaml", $dir);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $content);
    }

    protected function configExists()
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/config.yaml", realpath($home));
        return file_exists($path);
    }

    private function askQuestion($text, $default = null)
    {
        $defaultValueText = sprintf('(%s)', (string)$default);

        $question = new Question(sprintf($text, $defaultValueText), $default);
        return $this->questionHelper->ask($this->input, $this->output, $question);
    }

    /**
     * @param bool $allowOverwrite
     */
    public function setAllowOverwrite(bool $allowOverwrite): void
    {
        $this->allowOverwrite = $allowOverwrite;
    }
}

<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Env;
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
            $question = new Question('file "~/.enhavo/config.yml" exists, overwrite? [y/n]', 'n');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);
            if ($option !== 'y') {
                return Command::SUCCESS;
            }
            $configuration = (new Factory())->create();
            return $this->createConfigFile($configuration);
        }

        while(true) {
            $question = new Question('create config under "~/.enhavo/config.yml"? [y/n]', 'y');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);

            if (strtolower($option) === 'n') {
                return Command::SUCCESS;
            } elseif (strtolower($option) === 'y') {
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
        if ($value = $this->ask('default user email %s?', $configuration?$configuration->getDefaultUserEmail():null)) {
            $defaults['user_email'] = $value;
        }

        if ($value = $this->ask('default user password %s?', $configuration?$configuration->getDefaultUserPassword():null)) {
            $defaults['user_password'] = $value;
        }

        // database
        if ($value = $this->ask('database user %s?', $configuration?$configuration->getDefaultDatabaseUser():'root')) {
            $defaults['database_user'] = $value;
        }

        if ($value = $this->ask('database password %s?', $configuration?$configuration->getDefaultDatabasePassword():'root')) {
            $defaults['database_password'] = $value;
        }

        if ($value = $this->ask('database host %s?', $configuration?$configuration->getDefaultDatabaseHost():'localhost')) {
            $defaults['database_host'] = $value;
        }

        if ($value = $this->ask('database port %s?', $configuration?$configuration->getDefaultDatabasePort():3306)) {
            $defaults['database_port'] = intval($value);
        }

        // mailer
        if ($value = $this->ask('MAILER_URL %s?', $this->findEnv('MAILER_URL', $configuration))) {
            $defaults['env']['MAILER_URL'] = $value;
        }

        if ($value = $this->ask('MAILER_FROM %s?', $this->findEnv('MAILER_FROM', $configuration))) {
            $defaults['env']['MAILER_FROM'] = $value;
        }

        if ($value = $this->ask('MAILER_TO %s?', $this->findEnv('MAILER_TO', $configuration))) {
            $defaults['env']['MAILER_TO'] = $value;
        }

        if ($value = $this->ask('MAILER_DELIVERY_ADDRESS %s?', $this->findEnv('MAILER_DELIVERY_ADDRESS', $configuration)??$defaults['env']['MAILER_TO'])) {
            $defaults['env']['MAILER_DELIVERY_ADDRESS'] = $value;
        }

        // env
        if ($value = $this->ask('APP_ENV %s?', $this->findEnv('APP_ENV', $configuration)??'dev')) {
            $defaults['env']['APP_ENV'] = $value;
        }

        $content = Yaml::dump(['defaults' => $defaults], 3, 4);

        $this->writeFile($content);

        return Command::SUCCESS;
    }

    protected function findEnv($key, ?Configuration $configuration)
    {
        $env = $configuration ? $configuration->getDefaultEnv() : null;

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
        $path = sprintf("%s/config.yml", $dir);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $content);
    }

    protected function configExists()
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/config.yml", realpath($home));
        return file_exists($path);
    }

    private function ask($text, $default = null)
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

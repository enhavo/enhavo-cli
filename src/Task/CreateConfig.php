<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
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
            $question = new Question('file "~/.enhavo/config.yml" exits, overwrite? [y/n]', 'n');
            $option = $this->questionHelper->ask($this->input, $this->output, $question);
            if ($option !== 'y') {
                return Command::SUCCESS;
            }
            return $this->createConfigFile();
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

    private function createConfigFile()
    {
        $defaults = [
            'env' => []
        ];

        // user
        if ($value = $this->ask('default user email ?')) {
            $defaults['user_email'] = $value;
        }

        if ($value = $this->ask('default user password ?')) {
            $defaults['user_password'] = $value;
        }

        // database
        if ($value = $this->ask('database user ?')) {
            $defaults['database_user'] = $value;
        }

        if ($value = $this->ask('database password ?')) {
            $defaults['database_password'] = $value;
        }

        if ($value = $this->ask('database host ?')) {
            $defaults['database_host'] = $value;
        }

        if ($value = $this->ask('database port ?')) {
            $defaults['database_port'] = intval($value);
        }

        // mailer
        if ($value = $this->ask('MAILER_URL ?')) {
            $defaults['env']['MAILER_URL'] = $value;
        }

        if ($value = $this->ask('MAILER_FROM ?')) {
            $defaults['env']['MAILER_FROM'] = $value;
        }

        if ($value = $this->ask('MAILER_TO ?')) {
            $defaults['env']['MAILER_TO'] = $value;
        }

        if ($value = $this->ask('MAILER_DELIVERY_ADDRESS ?')) {
            $defaults['env']['MAILER_DELIVERY_ADDRESS'] = $value;
        }

        // env
        if ($value = $this->ask('APP_ENV ?')) {
            $defaults['env']['APP_ENV'] = $value;
        }

        $content = Yaml::dump(['defaults' => $defaults], 3, 4);

        $this->writeFile($content);

        return Command::SUCCESS;
    }

    protected function writeFile($content)
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/config.yml", realpath($home));
        file_put_contents($path, $content);
    }

    protected function configExists()
    {
        $home = getenv("HOME");
        $path = sprintf("%s/.enhavo/config.yml", realpath($home));
        return file_exists($path);
    }

    private function ask($text)
    {
        $question = new Question($text);
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

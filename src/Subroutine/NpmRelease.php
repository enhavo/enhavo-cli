<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class NpmRelease extends AbstractSubroutine
{
    /** @var Configuration */
    private $configuration;

    /** @var string */
    private $version;

    /** @var bool */
    private $privateAccess;

    /**
     * NpmRelease constructor.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param Configuration $configuration
     * @param string $version
     * @param bool $privateAccess
     */
    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, Configuration $configuration, string $version, bool $privateAccess)
    {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
        $this->version = $version;
        $this->privateAccess = $privateAccess;
    }


    public function __invoke(): int
    {
        $success = $this->checkConfig();
        if (!$success) {
            return Command::FAILURE;
        }

        $success = $this->updateVersion();
        if (!$success) {
            return Command::FAILURE;
        }

        $success = $this->publish();
        if (!$success) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function checkConfig()
    {
        if ($this->configuration->getNpmToken() === null) {
            $this->output->writeln('No npm token was set. Define it in your .enhavo.yml or set env var NPM_TOKEN');
            return false;
        }

        return true;
    }

    private function updateVersion()
    {
        if (!file_exists('package.json')) {
            $this->output->writeln('No package.json found');
            return false;
        }

        $version = str_replace('v', '', $this->version);
        $packageJson = \json_decode(file_get_contents('package.json'), true);
        $packageJson['version'] = $version;
        file_put_contents('package.json', json_encode($packageJson, JSON_PRETTY_PRINT));

        return true;
    }

    private function publish()
    {
        $url = sprintf('//%s/:_authToken="%s"', $this->configuration->getNpmRegistry(), $this->configuration->getNpmToken());
        file_put_contents('.npmrc', $url);
        $this->execute(['npm', 'publish', '--access', $this->privateAccess ? 'private' : 'public']);
        unlink('.npmrc');

        return true;
    }

    private function execute($command)
    {
        $process = new Process($command);
        $process->run();
        if ($process->isSuccessful()) {
            $output = $process->getOutput();
            $this->output->writeln($output);
        } else {
            $output = $process->getErrorOutput();
            $this->output->writeln($output);
        }
    }
}

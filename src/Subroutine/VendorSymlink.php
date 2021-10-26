<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Factory;
use Enhavo\Component\Cli\SubroutineInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class VendorSymlink extends AbstractSubroutine implements SubroutineInterface
{
    /** @var string */
    private $packageName;

    /** @var Filesystem */
    private $fs;

    /** @var Configuration */
    private $configuration;

    /** @var Factory */
    private $factory;

    /**
     * NpmRelease constructor.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param string $packageName
     * @param Configuration $configuration
     */
    public function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper, $packageName, Configuration $configuration, Factory $factory)
    {
        parent::__construct($input, $output, $questionHelper);
        $this->packageName = $packageName;
        $this->fs = new Filesystem();
        $this->configuration = $configuration;
        $this->factory = $factory;
    }

    public function __invoke(): int
    {
        $path = $this->checkPackage();
        if ($path === false) {
            $this->output->writeln(sprintf('Package "%s" not found', $this->packageName));
            return Command::FAILURE;
        }

        $this->output->writeln(sprintf('Package "%s" found', $this->packageName));

        $mainRepositoryNames = $this->getMainRepositoryNames();
        if (count($mainRepositoryNames) === 0) {
            $this->output->writeln(sprintf('No main repository defined in enhavo configuration'));
            return Command::FAILURE;
        }

        $mainPackagePath = $this->findMainPackagePath();
        if ($mainPackagePath === null) {
            $this->output->writeln(sprintf('Package "%s" could not be found in any main repositories: "%s"', $this->packageName, join(',', $mainRepositoryNames)));
            return Command::FAILURE;
        }

        $this->fs->remove($path);
        $this->fs->symlink($mainPackagePath, $path);

        $this->output->writeln(sprintf('Package "%s" was symlinked to "%s"', $this->packageName, $mainPackagePath));

        return Command::SUCCESS;
    }

    private function checkPackage()
    {
        $path = sprintf('%s/vendor/%s', getcwd(), $this->packageName);
        if ($this->fs->exists($path)) {
            return $path;
        } else {
            return false;
        }
    }

    private function getMainRepositoryNames(): array
    {
        $names = [];

        $mainRepositories = $this->configuration->getMainRepositories();
        foreach ($mainRepositories as $name => $mainRepository) {
            $names[] = $name;
        }

        return $names;
    }

    private function findMainPackagePath()
    {
        $mainRepositories = $this->configuration->getMainRepositories();

        foreach ($mainRepositories as $mainRepository) {
            $configPath = sprintf('%s/.enhavo.yaml', $mainRepository);
            if(!$this->fs->exists($configPath)) {
                $configPath = sprintf('%s/.enhavo.yml', $mainRepository);
            }

            if ($this->fs->exists($configPath)) {
                $configuration = new Configuration();
                $this->factory->readFromFile($configPath, $configuration);
                foreach ($configuration->getSubtrees() as $subtree) {
                    if ($subtree->getPackage() === $this->packageName) {
                        return sprintf('%s/%s', $mainRepository, $subtree->getPrefix());
                    }
                }
            }
        }

        return null;
    }
}

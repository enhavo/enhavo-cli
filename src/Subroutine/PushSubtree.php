<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Git\Git;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushSubtree extends AbstractSubroutine
{
    /** @var Configuration */
    private $configuration;

    /** @var string */
    private $workspace;

    /** @var string */
    private $remote;

    /** @var string */
    private $branch;

    /** @var bool */
    private $force;

    /**
     * PushSubtree constructor.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param Configuration $configuration
     * @param string $workspace
     * @param string $remote
     * @param string $branch
     * @param bool $force
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        Configuration $configuration,
        string $workspace,
        string $remote,
        string $branch,
        bool $force
    ) {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
        $this->workspace = $workspace;
        $this->remote = $remote;
        $this->branch = $branch;
        $this->force = $force;
    }


    public function distribute()
    {
        $workspace = realpath($this->workspace);

        $this->checkWorkspace($workspace);

        $git = new Git($workspace, $this->configuration->getSubtrees());

        if (!$git->exists()) {
            // Repository not exists ... cloning
            $git->clone();
        }

        $git->fetch();

        // set remotes
        foreach ($this->configuration->getSubtrees() as $subtree) {
            if ($this->remote && $this->remote != $subtree->getName()) {
                continue;
            }

            if (!$git->hasRemote($subtree->getName())) {
                // Add remote to main repository;
                $git->addRemote($subtree->getName(), $subtree->getRepository());
            }
        }

        // push branches
        $branches = $git->getBranches();
        foreach ($branches as $branch) {
            if ($this->remote && $branch != $this->remote) {
                continue;
            }

            foreach ($this->configuration->getSubtrees() as $distribute) {
                if ($this->remote && $this->remote != $distribute->getName()) {
                    continue;
                }

                // Push branch to remote
                $git->pushBranch($distribute->getName(), $distribute->getPath(), $branch, $this->force);
            }
        }

        // push tags
        $tags = $git->getTags();
        foreach ($tags as $tag) {
            if ($this->remote && $tag != $this->remote) {
                continue;
            }

            foreach ($this->configuration->getSubtrees() as $distribute) {
                if ($this->remote && $this->remote != $distribute->getName()) {
                    continue;
                }

                if (!$git->hasTag($distribute->getName(), $tag)) {
                    $git->pushTag($distribute->getName(), $distribute->getPath(), $tag);
                }
            }
        }
    }

    private static function checkWorkspace(string $workspace)
    {
        if(!file_exists($workspace)) {
            mkdir($workspace, 0777, true);
        }
    }
}

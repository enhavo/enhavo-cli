<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Subtree;
use Enhavo\Component\Cli\Git\Git;
use Enhavo\Component\Cli\Task\DownloadSplitSh;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PushSubtree extends AbstractSubroutine
{
    /** @var Configuration */
    private $configuration;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $tag;

    /** @var string|null */
    private $branch;

    /** @var bool|null */
    private $force;

    /**
     * PushSubtree constructor.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $questionHelper
     * @param Configuration $configuration
     * @param string|null $name
     * @param bool|null $force
     * @param string|null $branch
     * @param string|null $tag
     */
    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $questionHelper,
        Configuration $configuration,
        ?string $name,
        ?bool $force,
        ?string $branch,
        ?string $tag
    ) {
        parent::__construct($input, $output, $questionHelper);
        $this->configuration = $configuration;
        $this->name = $name;
        $this->force = $force;
        $this->branch = $branch;
        $this->tag = $tag;
    }

    public function __invoke()
    {
        if (!count($this->configuration->getSubtrees())) {
            $this->output->writeln(sprintf('No subtrees configured'));
            return Command::FAILURE;
        }

        $splitShPath = (new DownloadSplitSh($this->input, $this->output, $this->questionHelper))();
        $git = new Git(getcwd());
        if ($splitShPath !== null) {
            $git->setSplitShBin($splitShPath);
        }

        $subtrees = [];
        foreach ($this->configuration->getSubtrees() as $subtree) {
            if ($this->name && $this->name === $subtree->getName()) {
                $subtrees[] = $subtree;
                continue;
            } elseif ($this->name === null) {
                $subtrees[] = $subtree;
            }
        }

        foreach ($subtrees as $subtree) {
            if (!$git->hasRemote($subtree->getName())) {
                // Add remote to main repository;
                $git->addRemote($subtree->getName(), $subtree->getUrl());
            }
        }

        $branch = $this->branch !== null ? $this->branch : $git->getCurrentBranch();
        foreach ($subtrees as $subtree) {
            $git->pushSubtreeBranch($subtree->getName(), $subtree->getPrefix(), $branch);
        }

        if ($this->tag) {
            $this->pushTag($subtrees, $git);
        } else {
            $this->pushBranch($subtrees, $git);
        }

        return Command::SUCCESS;
    }

    /**
     * @param Subtree[] $subtrees
     * @param Git $git
     */
    private function pushBranch(array $subtrees, Git $git)
    {
        $branch = $this->branch !== null ? $this->branch : $git->getCurrentBranch();
        foreach ($subtrees as $subtree) {
            $this->output->writeln(sprintf('Push branch "%s" to remote "%s"', $branch, $subtree->getName()));
            $git->pushSubtreeBranch($subtree->getName(), $subtree->getPrefix(), $branch);
        }
    }

    /**
     * @param Subtree[] $subtrees
     * @param Git $git
     */
    private function pushTag(array $subtrees,  Git $git)
    {
        foreach ($subtrees as $subtree) {
            $this->output->writeln(sprintf('Push tag "%s" to remote "%s"', $this->tag, $subtree->getName()));
            $git->pushSubtreeTag($subtree->getName(), $subtree->getPrefix(), $this->tag);
        }
    }
}
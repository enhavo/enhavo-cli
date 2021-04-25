<?php

namespace Enhavo\Component\Cli\Subroutine;

use Enhavo\Component\Cli\AbstractSubroutine;
use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Subtree;
use Enhavo\Component\Cli\Git\Git;
use Enhavo\Component\Cli\Task\DownloadSplitSh;
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
        (new DownloadSplitSh($this->input, $this->output, $this->questionHelper))();
        $git = new Git(getcwd());
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
                $git->addRemote($subtree->getName(), $subtree->getRepository());
            }
        }
        $branch = $this->branch !== null ? $this->branch : $git->getCurrentBranch();
        foreach ($subtrees as $subtree) {
            $git->pushSubtreeBranch($subtree->getName(), $subtree->getPath(), $branch);
        }

        if ($this->tag) {
            $this->pushTag($subtrees, $git);
        } else {
            $this->pushBranch($subtrees, $git);
        }
    }

    /**
     * @param Subtree[] $subtrees
     * @param Git $git
     */
    private function pushBranch(array $subtrees, Git $git)
    {
        $branch = $this->branch !== null ? $this->branch : $git->getCurrentBranch();
        foreach ($subtrees as $subtree) {
            $git->pushSubtreeBranch($subtree->getName(), $subtree->getPath(), $branch);
        }
    }

    /**
     * @param Subtree[] $subtrees
     * @param Git $git
     */
    private function pushTag(array $subtrees,  Git $git)
    {
        foreach ($subtrees as $subtree) {
            $git->pushSubtreeTag($subtree->getName(), $subtree->getPath(), $this->tag);
        }
    }
}

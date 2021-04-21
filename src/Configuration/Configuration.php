<?php

namespace Enhavo\Component\Cli\Configuration;

class Configuration
{
    /** @var Subtree[] */
    private $subtrees = [];

    /**
     * @param Subtree $subtree
     */
    public function addSubtree(Subtree $subtree)
    {
        $this->subtrees[] = $subtree;
    }

    /**
     * @return Subtree[]
     */
    public function getSubtrees(): array
    {
        return $this->subtrees;
    }
}

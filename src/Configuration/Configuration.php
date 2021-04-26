<?php

namespace Enhavo\Component\Cli\Configuration;

class Configuration
{
    /** @var Subtree[] */
    private $subtrees = [];

    /** @var string|null */
    private $npmToken;

    /** @var string|null */
    private $npmRegistry = 'registry.npmjs.org';

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

    /**
     * @return string|null
     */
    public function getNpmToken(): ?string
    {
        return $this->npmToken;
    }

    /**
     * @param string|null $npmToken
     */
    public function setNpmToken(?string $npmToken): void
    {
        $this->npmToken = $npmToken;
    }

    /**
     * @return string|null
     */
    public function getNpmRegistry(): ?string
    {
        return $this->npmRegistry;
    }

    /**
     * @param string|null $npmRegistry
     */
    public function setNpmRegistry(?string $npmRegistry): void
    {
        $this->npmRegistry = $npmRegistry;
    }
}

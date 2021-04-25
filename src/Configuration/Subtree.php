<?php

namespace Enhavo\Component\Cli\Configuration;

class Subtree
{
    /** @var string */
    private $name;

    /** @var string */
    private $repository;

    /** @var string */
    private $path;

    /** @var bool */
    private $pushTag;

    /**
     * Subtree constructor.
     * @param string $name
     * @param string $repository
     * @param string $path
     * @param bool $pushTag
     */
    public function __construct(string $name, string $repository, string $path, bool $pushTag = true)
    {
        $this->name = $name;
        $this->repository = $repository;
        $this->path = $path;
        $this->pushTag = $pushTag;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRepository(): string
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}

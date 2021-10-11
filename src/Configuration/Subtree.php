<?php

namespace Enhavo\Component\Cli\Configuration;

class Subtree
{
    /** @var string */
    private $name;

    /** @var string */
    private $url;

    /** @var string */
    private $prefix;

    /** @var bool */
    private $pushTag;

    /** @var string|null */
    private $package;

    /**
     * Subtree constructor.
     * @param string $name
     * @param string $url
     * @param string $prefix
     * @param bool $pushTag
     */
    public function __construct(string $name, string $url, string $prefix, string $package = null, bool $pushTag = true)
    {
        $this->name = $name;
        $this->url = $url;
        $this->prefix = $prefix;
        $this->pushTag = $pushTag;
        $this->package = $package;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return bool
     */
    public function isPushTag(): bool
    {
        return $this->pushTag;
    }

    /**
     * @return ?string
     */
    public function getPackage(): ?string
    {
        return $this->package;
    }
}

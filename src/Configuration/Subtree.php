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

    /**
     * Subtree constructor.
     * @param string $name
     * @param string $url
     * @param string $prefix
     * @param bool $pushTag
     */
    public function __construct(string $name, string $url, string $prefix, bool $pushTag = true)
    {
        $this->name = $name;
        $this->url = $url;
        $this->prefix = $prefix;
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
}

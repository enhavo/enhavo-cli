<?php

namespace Enhavo\Component\Cli\Git;

class Commit
{
    /** @var string */
    private $message;

    /** @var string */
    private $hash;

    /**
     * Commit constructor.
     * @param string $message
     * @param string $hash
     */
    public function __construct(string $message, string $hash)
    {
        $this->message = $message;
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
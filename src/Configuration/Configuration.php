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

    /** @var Env[] */
    private $defaultEnv = [];

    /** @var string|null */
    private $defaultDatabaseUser;

    /** @var string|null */
    private $defaultDatabasePassword;

    /** @var string|null */
    private $defaultDatabaseHost;

    /** @var string|null */
    private $defaultDatabasePort;

    /** @var string|null */
    private $defaultUserEmail;

    /** @var string|null */
    private $defaultUserPassword;

    /** @var string[] */
    private $mainRepositories = [];

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

    /**
     * @param Env $defaultEnv
     */
    public function addDefaultEnv(Env $defaultEnv)
    {
        $this->defaultEnv[] = $defaultEnv;
    }

    /**
     * @return Env[]
     */
    public function getDefaultEnv(): array
    {
        return $this->defaultEnv;
    }

    /**
     * @return string|null
     */
    public function getDefaultDatabaseUser(): ?string
    {
        return $this->defaultDatabaseUser;
    }

    /**
     * @param string|null $defaultDatabaseUser
     */
    public function setDefaultDatabaseUser(?string $defaultDatabaseUser): void
    {
        $this->defaultDatabaseUser = $defaultDatabaseUser;
    }

    /**
     * @return string|null
     */
    public function getDefaultDatabasePassword(): ?string
    {
        return $this->defaultDatabasePassword;
    }

    /**
     * @param string|null $defaultDatabasePassword
     */
    public function setDefaultDatabasePassword(?string $defaultDatabasePassword): void
    {
        $this->defaultDatabasePassword = $defaultDatabasePassword;
    }

    /**
     * @return string|null
     */
    public function getDefaultDatabaseHost(): ?string
    {
        return $this->defaultDatabaseHost;
    }

    /**
     * @param string|null $defaultDatabaseHost
     */
    public function setDefaultDatabaseHost(?string $defaultDatabaseHost): void
    {
        $this->defaultDatabaseHost = $defaultDatabaseHost;
    }

    /**
     * @return string|null
     */
    public function getDefaultDatabasePort(): ?string
    {
        return $this->defaultDatabasePort;
    }

    /**
     * @param string|null $defaultDatabasePort
     */
    public function setDefaultDatabasePort(?string $defaultDatabasePort): void
    {
        $this->defaultDatabasePort = $defaultDatabasePort;
    }

    /**
     * @return string|null
     */
    public function getDefaultUserEmail(): ?string
    {
        return $this->defaultUserEmail;
    }

    /**
     * @param string|null $defaultUserEmail
     */
    public function setDefaultUserEmail(?string $defaultUserEmail): void
    {
        $this->defaultUserEmail = $defaultUserEmail;
    }

    /**
     * @return string|null
     */
    public function getDefaultUserPassword(): ?string
    {
        return $this->defaultUserPassword;
    }

    /**
     * @param string|null $defaultUserPassword
     */
    public function setDefaultUserPassword(?string $defaultUserPassword): void
    {
        $this->defaultUserPassword = $defaultUserPassword;
    }

    /**
     * @return string[]
     */
    public function getMainRepositories(): array
    {
        return $this->mainRepositories;
    }

    /**
     * @param string $mainRepository
     */
    public function addMainRepository(string $mainRepository)
    {
        $this->mainRepositories[] = $mainRepository;
    }

    /**
     * @param string $mainRepository
     */
    public function removeMainRepository(string $mainRepository)
    {
        if (false !== $key = array_search($mainRepository, $this->mainRepositories, true)) {
            array_splice($this->mainRepositories, $key, 1);
        }
    }
}

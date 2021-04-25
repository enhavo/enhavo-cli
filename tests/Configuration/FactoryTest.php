<?php

namespace Enhavo\Component\Cli\Tests\Configuration;

use Enhavo\Component\Cli\Configuration\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testSubtreesCreate()
    {
        $factory = new TestFactory();
        $factory->localFile = __DIR__.'/../fixtures/configuration/subtrees.yml';

        $configuration = $factory->create();

        $this->assertCount(2, $configuration->getSubtrees());
        $this->assertEquals('repo_one', $configuration->getSubtrees()[0]->getName());
        $this->assertEquals('git@github.com:test/repo_one', $configuration->getSubtrees()[0]->getUrl());
        $this->assertEquals('src/RepoOne', $configuration->getSubtrees()[0]->getPrefix());
        $this->assertTrue($configuration->getSubtrees()[0]->isPushTag());
        $this->assertFalse($configuration->getSubtrees()[1]->isPushTag());
    }
}


class TestFactory extends Factory
{
    /** @var string */
    public $localFile;
    /** @var string */
    public $globalFile;

    protected function findGlobalConfigFile()
    {
        if ($this->globalFile) {
            return $this->globalFile;
        }
        return null;
    }

    protected function findLocalConfigFile()
    {
        if ($this->localFile) {
            return $this->localFile;
        }
        return null;
    }
}
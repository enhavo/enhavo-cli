<?php

namespace Enhavo\Component\Cli\Tests\Task;

use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Configuration\Env;
use Enhavo\Component\Cli\Task\CreateEnv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CreateEnvTest extends TestCase
{
    public function createInstance(CreateEnvDependencies $dependencies)
    {
        $instance = new CreateEnvInstance($dependencies->input, $dependencies->output, $dependencies->questionHelper, $dependencies->configuration);
        return $instance;
    }

    public function createDependencies()
    {
        $dependencies = new CreateEnvDependencies();
        $dependencies->input = $this->getMockBuilder(InputInterface::class)->getMock();
        $dependencies->output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $dependencies->questionHelper = $this->getMockBuilder(QuestionHelper::class)->disableOriginalConstructor()->getMock();
        $dependencies->configuration = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock();
        return $dependencies;
    }

    private function deleteFile($file)
    {
        $fs = new Filesystem();
        if ($fs->exists($file)) {
            $fs->remove($file);
        }
    }

    public function testEmpty()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->once())->method('ask')->willReturn('y');
        $instance = $this->createInstance($dependencies);
        $this->deleteFile($instance->exposeFile('.env.local'));
        $instance();
        $this->assertEquals("", file_get_contents($instance->exposeFile('.env.local')));
    }

    public function testWithDefaultEnv()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->once())->method('ask')->willReturn('y');
        $dependencies->configuration->method('getDefaultEnv')->willReturn([
            new Env('APP_ENV', 'dev'),
            new Env('MAILER_FROM', 'peter@pan.de'),
            new Env('FOO', 'bar'),
        ]);
        $instance = $this->createInstance($dependencies);
        $this->deleteFile($instance->exposeFile('.env.local'));
        $instance();
        $this->assertEquals("APP_ENV=\"dev\"\nMAILER_FROM=\"peter@pan.de\"\n", file_get_contents($instance->exposeFile('.env.local')));
    }

    public function testWithDatabaseValues()
    {
        $dependencies = $this->createDependencies();
        $answers = ['y', 'hook'];
        $dependencies->questionHelper->method('ask')->willReturnCallback(function () use (&$answers) {
            return array_shift($answers);
        });

        $dependencies->configuration->method('getDefaultDatabaseUser')->willReturn('peter');
        $dependencies->configuration->method('getDefaultDatabasePassword')->willReturn('w3ndy');
        $dependencies->configuration->method('getDefaultDatabasePort')->willReturn('1337');
        $dependencies->configuration->method('getDefaultDatabaseHost')->willReturn('wonderland');

        $instance = $this->createInstance($dependencies);
        $this->deleteFile($instance->exposeFile('.env.local'));
        $instance();
        $this->assertEquals("DATABASE_URL=\"mysql://peter:w3ndy@wonderland:1337/hook\"\n", file_get_contents($instance->exposeFile('.env.local')));
    }
}

class CreateEnvDependencies
{
    /** @var InputInterface|MockObject */
    public $input;
    /** @var OutputInterface|MockObject */
    public $output;
    /** @var QuestionHelper|MockObject */
    public $questionHelper;
    /** @var Configuration|MockObject */
    public $configuration;
}

class CreateEnvInstance extends CreateEnv
{
    public $directory = 'default';

    protected function getFile($file)
    {
        return $this->exposeFile($file);
    }

    public function exposeFile($file)
    {
        return sprintf('%s/../fixtures/env/%s/%s', __DIR__, $this->directory, $file);
    }
}

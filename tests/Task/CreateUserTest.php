<?php

namespace Enhavo\Component\Cli\Tests\Task;

use Enhavo\Component\Cli\Configuration\Configuration;
use Enhavo\Component\Cli\Task\CreateUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserTest extends TestCase
{
    public function createInstance(CreateUserTestDependencies $dependencies)
    {
        $instance = new CreateUserInstance($dependencies->input, $dependencies->output, $dependencies->questionHelper, $dependencies->configuration);
        return $instance;
    }

    public function createDependencies()
    {
        $dependencies = new CreateUserTestDependencies();
        $dependencies->input = $this->getMockBuilder(InputInterface::class)->getMock();
        $dependencies->output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $dependencies->questionHelper = $this->getMockBuilder(QuestionHelper::class)->disableOriginalConstructor()->getMock();
        $dependencies->configuration = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()->getMock();
        return $dependencies;
    }

    public function testAsk()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->once())->method('ask')->willReturn('y');

        $instance = $this->createInstance($dependencies);
        $instance();

        $this->assertCount(2, $instance->execute);
        $this->assertEquals($instance->execute[0], 'enhavo:user:create');
        $this->assertEquals($instance->execute[1], '--super-admin');
    }

    public function testAskDefault()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->exactly(2))->method('ask')->willReturn('y');
        $dependencies->configuration->method('getDefaultUserEmail')->willReturn('peter@pan.com');
        $dependencies->configuration->method('getDefaultUserPassword')->willReturn('w3ndy');

        $instance = $this->createInstance($dependencies);
        $instance->setAskDefault(true);
        $instance();

        $this->assertCount(4, $instance->execute);
        $this->assertEquals($instance->execute[0], 'enhavo:user:create');
        $this->assertEquals($instance->execute[1], 'peter@pan.com');
        $this->assertEquals($instance->execute[2], 'w3ndy');
        $this->assertEquals($instance->execute[3], '--super-admin');
    }

    public function testNoAsk()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->never())->method('ask');
        $dependencies->configuration->method('getDefaultUserEmail')->willReturn('peter@pan.com');
        $dependencies->configuration->method('getDefaultUserPassword')->willReturn('w3ndy');

        $instance = $this->createInstance($dependencies);
        $instance->setAsk(false);
        $instance();

        $this->assertCount(4, $instance->execute);
        $this->assertEquals($instance->execute[0], 'enhavo:user:create');
        $this->assertEquals($instance->execute[1], 'peter@pan.com');
        $this->assertEquals($instance->execute[2], 'w3ndy');
        $this->assertEquals($instance->execute[3], '--super-admin');
    }

    public function testNoCreateUser()
    {
        $dependencies = $this->createDependencies();
        $dependencies->questionHelper->expects($this->once())->method('ask')->willReturn('n');

        $instance = $this->createInstance($dependencies);
        $instance();

        $this->assertNull($instance->execute);
    }
}

class CreateUserTestDependencies
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

class CreateUserInstance extends CreateUser
{
    public $execute;
    public $exists = true;

    public function console(array $command, OutputInterface $output)
    {
        $this->execute = $command;
    }

    public function existsConsoleCommand($command)
    {
        return $this->exists;
    }
}
<?php

namespace Enhavo\Component\Cli\Tests\Task;

use Enhavo\Component\Cli\Task\CreateConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateConfigTest extends TestCase
{
    public function createInstance(CreateConfigTestDependencies $dependencies)
    {
        $instance = new CreateConfigInstance($dependencies->input, $dependencies->output, $dependencies->questionHelper);
        return $instance;
    }

    public function createDependencies()
    {
        $dependencies = new CreateConfigTestDependencies();
        $dependencies->input = $this->getMockBuilder(InputInterface::class)->getMock();
        $dependencies->output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $dependencies->questionHelper = $this->getMockBuilder(QuestionHelper::class)->disableOriginalConstructor()->getMock();
        return $dependencies;
    }

    private function getConfigContent($file)
    {
        return file_get_contents(sprintf(__DIR__.'/../fixtures/configuration/%s', $file));
    }

    public function testCreate()
    {
        $dependencies = $this->createDependencies();

        $answers = [
            'y',
            'peter@pan.com',
            'w3ndy',
            'root',
            's3cr3t',
            'wonderland',
            '3306',
            'mailer:something',
            'no-reply@pan.com',
            'info@pan.com',
            'dev@pan.com',
            'dev',
        ];
        $dependencies->questionHelper->method('ask')->willReturnCallback(function () use (&$answers) {
            return array_shift($answers);
        });

        $instance = $this->createInstance($dependencies);
        $instance();

        $this->assertEquals($this->getConfigContent('expect-config.yml'), $instance->content);
    }
}

class CreateConfigTestDependencies
{
    /** @var InputInterface|MockObject */
    public $input;
    /** @var OutputInterface|MockObject */
    public $output;
    /** @var QuestionHelper|MockObject */
    public $questionHelper;
}

class CreateConfigInstance extends CreateConfig
{
    public $exists = false;
    public $content;

    protected function configExists()
    {
        return $this->exists;
    }

    protected function writeFile($content)
    {
        return $this->content = $content;
    }
}

<?php

namespace Enhavo\Component\Cli\Tests\Task;

use Enhavo\Component\Cli\Task\MigrateEnhavoResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class MigrateEnhavoResourceTest extends TestCase
{
    public function createInstance(MigrateEnhavoResourceDependencies $dependencies)
    {
        $instance = new MigrateEnhavoResource($dependencies->input, $dependencies->output, $dependencies->questionHelper);
        return $instance;
    }

    public function createDependencies()
    {
        $dependencies = new MigrateEnhavoResourceDependencies();
        $dependencies->input = $this->getMockBuilder(InputInterface::class)->getMock();
        $dependencies->output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $dependencies->questionHelper = $this->getMockBuilder(QuestionHelper::class)->disableOriginalConstructor()->getMock();
        $dependencies->cwd = __DIR__ . '/../fixtures/migrate-enhavo-resource';
        return $dependencies;
    }

    public function setDefaultInputs(MigrateEnhavoResourceDependencies $dependencies)
    {
        $dependencies->input->method('getArgument')->willReturnCallback(function ($name) {
            if ($name == 'resource_file') {
                return 'original/resources.yaml';
            } else if ($name == 'routes_dir') {
                return 'original/routes';
            } else if ($name == 'template_dir') {
                return 'original/templates';
            }
            throw new \Exception();
        });
    }

    public function resetDefaultDirs()
    {
        $cwd = __DIR__ . '/../fixtures/migrate-enhavo-resource';
        $fs = new Filesystem();

        if ($fs->exists($cwd.'/config')) {
            $fs->remove($cwd.'/config');
        }

        $fs->mkdir($cwd.'/config/resources');
        $fs->mkdir($cwd.'/config/routes/admin_api');
        $fs->mkdir($cwd.'/config/routes/admin');
    }

    public function testFilesContent()
    {
        $this->resetDefaultDirs();
        $dependencies = $this->createDependencies();
        $this->setDefaultInputs($dependencies);
        $instance = $this->createInstance($dependencies);
        $instance($dependencies->cwd);

        $files = [
            'resources/article.yaml',
            'routes/admin/article.yaml',
            'routes/admin_api/article.yaml',
        ];

        foreach ($files as $file) {
            $migratedFile =  __DIR__ . '/../fixtures/migrate-enhavo-resource/migrated/'.$file;
            $generatedFile =  __DIR__ . '/../fixtures/migrate-enhavo-resource/config/'.$file;

            $this->assertTrue(file_exists($generatedFile));
            $this->assertEquals(
                file_get_contents($migratedFile),
                file_get_contents($generatedFile),
                'Expecting content on file: '.$file
            );
        }
    }
}


class MigrateEnhavoResourceDependencies
{
    /** @var InputInterface|MockObject */
    public $input;
    /** @var OutputInterface|MockObject */
    public $output;
    /** @var QuestionHelper|MockObject */
    public $questionHelper;
    /** @var string */
    public $cwd;
}

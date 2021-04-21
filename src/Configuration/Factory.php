<?php

namespace Enhavo\Component\Cli\Configuration;

use Symfony\Component\Yaml\Yaml;

class Factory
{
    public function create(): Configuration
    {
        $configuration = new Configuration();
        $configFile = $this->findConfigFile();
        if ($configFile) {
            $this->readFromFile($configFile, $configuration);
        }
        return $configuration;
    }

    private function findConfigFile()
    {
        if (getcwd() === false) {
            return null;
        }
        $configFile = sprintf('%s/.enhavo.yml', getcwd());
        if (file_exists($configFile)) {
            return $configFile;
        }
        return null;
    }

    private function readFromFile(string $path, Configuration $configuration)
    {
        $content = file_get_contents($path);
        $config = Yaml::parse($content);

        if (isset($config['subtrees'])) {
            foreach($config['subtrees'] as $subtree) {
                $configuration->addSubtree(new Subtree($subtree['name'], $subtree['repository'], $subtree['path']));
            }
        }
    }
}

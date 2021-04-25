<?php

namespace Enhavo\Component\Cli\Configuration;

use Symfony\Component\Yaml\Yaml;

class Factory
{
    public function create(): Configuration
    {
        $configuration = new Configuration();

        $configFile = $this->findGlobalConfigFile();
        if ($configFile) {
            $this->readFromFile($configFile, $configuration);
        }

        $configFile = $this->findLocalConfigFile();
        if ($configFile) {
            $this->readFromFile($configFile, $configuration);
        }

        $this->readFromEnv($configuration);

        return $configuration;
    }

    private function findGlobalConfigFile()
    {
        $configFile = '~/.enhavo/config.yml';
        if (file_exists($configFile)) {
            return $configFile;
        }
        return null;
    }

    private function findLocalConfigFile()
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
                $configuration->addSubtree(new Subtree($subtree['name'], $subtree['repository'], $subtree['path'], isset($subtree['path']) ? $subtree['path'] : true));
            }
        }

        if (isset($config['npm']['token'])) {
            $configuration->setNpmToken($config['npm']['token']);
        }

        if (isset($config['npm']['registry'])) {
            $configuration->setNpmRegistry($config['npm']['registry']);
        }
    }

    private function readFromEnv(Configuration $configuration)
    {
        $npmUser = getenv('NPM_TOKEN');
        if ($npmUser) {
            $configuration->setNpmToken($npmUser);
        }

        $npmRegistry = getenv('NPM_REGISTRY');
        if ($npmRegistry) {
            $configuration->setNpmRegistry($npmRegistry);
        }
    }
}

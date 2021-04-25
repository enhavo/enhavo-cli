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

    protected function findGlobalConfigFile()
    {
        $home = getenv('HOME');
        $configFile = sprintf('%s/.enhavo/config.yml', $home);
        if (file_exists($configFile)) {
            return $configFile;
        }
        return null;
    }

    protected function findLocalConfigFile()
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
            foreach($config['subtrees'] as $name => $subtree) {
                $configuration->addSubtree(new Subtree($name, $subtree['url'], $subtree['prefix'], isset($subtree['push_tag']) ? $subtree['push_tag'] : true));
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

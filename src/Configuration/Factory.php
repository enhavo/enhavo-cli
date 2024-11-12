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
        $configFile = sprintf('%s/.enhavo/config.yaml', $home);
        if (file_exists($configFile)) {
            return $configFile;
        }
        $configFile = sprintf('%s/.enhavo/config.yaml', $home);
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
        $configFile = sprintf('%s/.enhavo.yaml', getcwd());
        if (file_exists($configFile)) {
            return $configFile;
        }
        $configFile = sprintf('%s/.enhavo.yml', getcwd());
        if (file_exists($configFile)) {
            return $configFile;
        }
        return null;
    }

    public function readFromFile(string $path, Configuration $configuration)
    {
        $content = file_get_contents($path);
        $config = Yaml::parse($content);

        if (isset($config['subtrees'])) {
            foreach($config['subtrees'] as $name => $subtree) {
                $configuration->addSubtree(new Subtree(
                    $name,
                    $subtree['url'],
                    $subtree['prefix'],
                    isset($subtree['package']) ? $subtree['package'] : null,
                    isset($subtree['push_tag']) ? $subtree['push_tag'] : true
                ));
            }
        }

        if (isset($config['npm']['token'])) {
            $configuration->setNpmToken($config['npm']['token']);
        }

        if (isset($config['npm']['registry'])) {
            $configuration->setNpmRegistry($config['npm']['registry']);
        }

        if (isset($config['defaults']['env'])) {
            foreach ($config['defaults']['env'] as $key => $value) {
                $configuration->addDefaultEnv(new Env($key, $value));
            }
        }

        if (isset($config['defaults']['user_email'])) {
            $configuration->setDefaultUserEmail($config['defaults']['user_email']);
        }

        if (isset($config['defaults']['user_password'])) {
            $configuration->setDefaultUserPassword($config['defaults']['user_password']);
        }

        if (isset($config['defaults']['database_host'])) {
            $configuration->setDefaultDatabaseHost($config['defaults']['database_host']);
        }

        if (isset($config['defaults']['database_user'])) {
            $configuration->setDefaultDatabaseUser($config['defaults']['database_user']);
        }

        if (isset($config['defaults']['database_password'])) {
            $configuration->setDefaultDatabasePassword($config['defaults']['database_password']);
        }

        if (isset($config['defaults']['database_port'])) {
            $configuration->setDefaultDatabasePort($config['defaults']['database_port']);
        }

        if (isset($config['main_repositories'])) {
            foreach ($config['main_repositories'] as $repository) {
                $configuration->addMainRepository($repository);
            }
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

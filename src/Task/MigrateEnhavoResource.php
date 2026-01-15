<?php

namespace Enhavo\Component\Cli\Task;

use Enhavo\Component\Cli\AbstractSubroutine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class MigrateEnhavoResource extends AbstractSubroutine
{
    private $routes = [];
    private $templateDir = null;
    private $cwd = null;
    private $force = false;


    public function __invoke($cwd)
    {
        $this->cwd = $cwd;
        $resourceFilePath = $this->input->getArgument('resource_file');
        $routesDirPath = $this->input->getArgument('routes_dir');
        $this->templateDir = $this->input->getArgument('template_dir');
        $this->force = $this->input->getOption('force');

        $resourceFilePath = strpos($resourceFilePath, '/') === 0 ? $resourceFilePath : $this->cwd.'/'.$resourceFilePath;
        $routesDirPath = strpos($routesDirPath, '/') === 0 ? $routesDirPath : $this->cwd.'/'.$routesDirPath;

        if (!file_exists($resourceFilePath)) {
            $this->output->writeln(sprintf('File "%s" not exists', $resourceFilePath));
            return Command::FAILURE;
        }

        if (!file_exists($routesDirPath)) {
            $this->output->writeln(sprintf('Dir "%s" not exists', $routesDirPath));
            return Command::FAILURE;
        }

        $content = Yaml::parse(file_get_contents($resourceFilePath));
        $syliusResources = $content['sylius_resource']['resources'] ?? [];

        $this->routes = $this->getRoutes($routesDirPath);

        foreach ($syliusResources as $resourceName => $resourceConfig) {
            $this->output->writeln(sprintf('Migrate resource "%s"', $resourceName));
            $this->migrationResource($resourceName, $resourceConfig);
        }

        return Command::SUCCESS;
    }

    private function getRoutes($routesDirPath)
    {
        $routes = [];
        $finder = new Finder();
        $finder->files()->in($routesDirPath);

        foreach ($finder as $file) {
            $filePathName = $file->getRealPath();
            $content = Yaml::parse(file_get_contents($filePathName));
            foreach ($content as $routeName => $routeConfiguration) {
                $routes[$routeName] = $routeConfiguration;
            }
        }

        return $routes;
    }

    private function getNewResourceFilePath($resourceName): string
    {
        $resourceFileName = $resourceName;
        $nameParts = explode('.', $resourceName);
        if (count($nameParts) === 2 && $nameParts[0] == 'app') {
            $resourceFileName = $nameParts[1];
        }
        return $this->cwd . '/config/resources/' . $resourceFileName . '.yaml';
    }

    private function getNewAdminRouteFilePath($resourceName): string
    {
        $resourceFileName = $resourceName;
        $nameParts = explode('.', $resourceName);
        if (count($nameParts) === 2 && $nameParts[0] == 'app') {
            $resourceFileName = $nameParts[1];
        }
        return $this->cwd . '/config/routes/admin/' . $resourceFileName . '.yaml';
    }

    private function getNewAdminApiRouteFilePath($resourceName): string
    {
        $resourceFileName = $resourceName;
        $nameParts = explode('.', $resourceName);
        if (count($nameParts) === 2 && $nameParts[0] == 'app') {
            $resourceFileName = $nameParts[1];
        }
        return  $this->cwd . '/config/routes/admin_api/' . $resourceFileName . '.yaml';
    }

    public function migrationResource($resourceName, $resourceConfig)
    {
        $indexRoute = $this->getRoute($resourceName, 'index');
        $createRoute = $this->getRoute($resourceName, 'create');
        $updateRoute = $this->getRoute($resourceName, 'update');
        $tableRoute = $this->getRoute($resourceName, 'table');
        $dataRoute = $this->getRoute($resourceName, 'data');
        $batchRoute = $this->getRoute($resourceName, 'batch');
        $autoComplete = $this->getRoute($resourceName, 'auto_complete');

        $gridActions = $indexRoute ? $this->getGridActions($indexRoute) : [];
        $createActions = $createRoute ? $this->getCreateActions($createRoute) : [];
        $collection = $tableRoute || $dataRoute ? $this->getCollection($tableRoute ?? $dataRoute) : [];
        $columns = $tableRoute || $dataRoute ? $this->getColumns($tableRoute ?? $dataRoute) : [];
        $filters = $tableRoute || $dataRoute ? $this->getFilters($tableRoute ?? $dataRoute) : [];
        $batches = $batchRoute ? $this->getBatches($batchRoute) : [];
        $tabs = $createRoute ? $this->getTabs($createRoute, $errors) : [];
        $form = $createRoute ? $this->getForm($createRoute, $resourceConfig) : $resourceConfig['classes']['form'];
        $formOptions = $createRoute ? $this->getFormOptions($createRoute) : [];
        $factoryMethod = $createRoute ? $this->getFactoryMethod($createRoute) : [];
        $factoryArguments = $createRoute ? $this->getFactoryArguments($createRoute) : [];

        $config = $this->getResourceConfig(
            $resourceName,
            $resourceConfig,
            $gridActions,
            $columns,
            $createActions,
            $tabs,
            $form,
            $formOptions,
            $filters,
            $batches,
            $dataRoute,
            $collection,
            $factoryMethod,
            $factoryArguments,
        );

        $newResourceFilePath = $this->getNewResourceFilePath($resourceName);
        if (!file_exists($newResourceFilePath) || $this->force) {
            $content = Yaml::dump($config, 8);
            file_put_contents($newResourceFilePath, $content);
            $this->output->writeln(sprintf('<comment>Write:</comment> %s', $newResourceFilePath));
        }

        $apiRoutes = $this->getApiRoutesConfig($resourceName, false, false, $autoComplete);
        $apiRoutesContent = [];
        $newAdminApiRouteFilePath = $this->getNewAdminApiRouteFilePath($resourceName);
        foreach ($apiRoutes as $key => $apiRoute) {
            $apiRoutesContent[] = Yaml::dump([$key => $apiRoute], 8);
        }

        if (count($apiRoutesContent) && (!file_exists($newAdminApiRouteFilePath) || $this->force)) {
            file_put_contents($newAdminApiRouteFilePath, implode("\n", $apiRoutesContent));
            $this->output->writeln(sprintf('<comment>Write:</comment> %s', $newAdminApiRouteFilePath));
        }

        $adminRoutes = $this->getAdminRoutesConfig($resourceName, false);
        $adminRoutesContent = [];
        $newAdminRouteFilePath = $this->getNewAdminRouteFilePath($resourceName);
        foreach ($adminRoutes as $key => $adminRoute) {
            $adminRoutesContent[] = Yaml::dump([$key => $adminRoute], 8);
        }

        if (count($apiRoutesContent) && (!file_exists($newAdminRouteFilePath) || $this->force)) {
            file_put_contents($newAdminRouteFilePath, implode("\n", $adminRoutesContent));
            $this->output->writeln(sprintf('<comment>Write:</comment> %s', $newAdminRouteFilePath));
        }

        if (!$indexRoute) {
            $this->output->writeln('<comment>No index route found!</comment>');
        }

        if (!$createRoute) {
            $this->output->writeln('<comment>No create route found!</comment>');
        }

        if (!$updateRoute) {
            $this->output->writeln('<comment>No update route found!</comment>');
        }

        if (!$tableRoute && !$dataRoute) {
            $this->output->writeln('<comment>No table or data route found!</comment>');
        }

        if (!$batchRoute) {
            $this->output->writeln('<comment>No batch route found!</comment>');
        }

        if ($updateRoute && $createRoute && $this->checkDifferentCreateUpdateTabs($updateRoute, $createRoute)) {
            $this->output->writeln('<comment>Create and update route have different tab options, maybe a second input is needed</comment>');
        }
    }

    private function getRoute($resourceName, $routeName)
    {
        $routePrefix = str_replace('.', '_', $resourceName);
        $routeName = $routePrefix . '_' . $routeName;
        return $this->routes[$routeName] ?? null;
    }

    private function getGridActions($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['actions'])) {
            return $defaults['_sylius']['viewer']['actions'];
        }

        return [];
    }

    private function getCreateActions($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['actions'])) {
            return $defaults['_sylius']['viewer']['actions'];
        }

        return [];
    }

    private function getColumns($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['columns'])) {
            $columns = [];
            foreach ($defaults['_sylius']['viewer']['columns'] as $key => $column) {
                if (isset($column['condition'])) {
                    $column['visible_condition'] = $column['condition'];
                    unset($column['condition']);
                }

                if ($column['type'] === 'property') {
                    $column['type'] = 'text';
                }

                if ($column['type'] === 'status') {
                    $column['type'] = 'state';
                    $column['property'] = $key;
                }

                $columns[$key] = $column;
            }

            return $columns;
        }

        return [];
    }

    private function getCollection($route)
    {
        $defaults = $route['defaults'] ?? [];
        $collection = [];
        if (isset($defaults['_sylius']['criteria'])) {
            $collection['criteria'] = $defaults['_sylius']['criteria'];
        }

        if (isset($defaults['_sylius']['sorting'])) {
            $collection['sorting'] = $defaults['_sylius']['sorting'];
        }

        return $collection;
    }

    private function getFilters($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['filters'])) {
            return $defaults['_sylius']['filters'];
        }

        return [];
    }

    private function getBatches($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['batches'])) {
            return $defaults['_sylius']['batches'];
        }

        return [];
    }

    private function getTabs($route, &$errors)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['tabs'])) {
            $tabs = $defaults['_sylius']['viewer']['tabs'];
            foreach ($tabs as $key => &$tab) {
                $tab['type'] = 'form';

                $arrangement = [];
                if (isset($tab['template']) && $this->templateDir) {
                    $path = $this->cwd . '/' . $this->templateDir . '/' . $tab['template'];

                    if (file_exists($path)) {
                        $content = file_get_contents($path);
                        $lines = explode("\n", $content);
                        foreach ($lines as $line) {
                            if (preg_match('/form_[a-z]+\(form.([a-zA-Z0-9_]+)\)/', $line, $matches)) {
                                $arrangement[] = $matches[1];
                            }
                        }
                    }
                }
                if (count($arrangement)) {
                    $tab['arrangement'] = $arrangement;
                }

                unset($tab['template']);
                if (isset($tab['full_width'])) {
                    unset($tab['full_width']);
                }
            }
            return $tabs;
        }

        return [];
    }

    private function getForm($route, $resourceConfig)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['form']['type'])) {
            return $defaults['_sylius']['viewer']['form']['type'];
        }

        return $resourceConfig['classes']['form'];
    }

    private function getFormOptions($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['viewer']['form']['options'])) {
            return $defaults['_sylius']['viewer']['form']['options'];
        }

        return [];
    }

    private function getFactoryMethod($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['factory']['method'])) {
            return $defaults['_sylius']['factory']['method'];
        }

        return [];
    }

    private function getFactoryArguments($route)
    {
        $defaults = $route['defaults'] ?? [];
        if (isset($defaults['_sylius']['factory']['arguments'])) {
            return $defaults['_sylius']['factory']['arguments'];
        }

        return [];
    }

    private function checkDifferentCreateUpdateTabs($createRoute, $updateRoute)
    {
        return false;
    }

    private function getResourceConfig(
        $resourceName,
        $resourceConfig,
        $gridActions,
        $columns,
        $createActions,
        $tabs,
        $form,
        $formOptions,
        $filters,
        $batches,
        $listRoute,
        $collection,
        $factoryMethod,
        $factoryArguments
    )
    {
        $classes = [];
        if (isset($resourceConfig['classes']['model'])) {
            $classes['model'] = $resourceConfig['classes']['model'];
        }

        if (isset($resourceConfig['classes']['factory'])) {
            $classes['factory'] = $resourceConfig['classes']['factory'];
        }

        if (isset($resourceConfig['classes']['repository'])) {
            $classes['repository'] = $resourceConfig['classes']['repository'];
        }

        $data = [
            'enhavo_resource' => [
                'resources' => [
                    $resourceName => [
                        'classes' => $classes
                    ],
                ],
                'grids' => [
                    $resourceName => [
                        'extends' => 'enhavo_resource.grid',
                        'resource' => $resourceName,
                        'collection' => $collection,
                        'actions' => $gridActions,
                        'filters' => $filters,
                        'columns' => $columns,
                        'batches' => $batches,
                    ],
                ],
                'inputs' => [
                    $resourceName => [
                        'extends' => 'enhavo_resource.input',
                        'resource' => $resourceName,
                        'form' => $form,
                        'form_options' => $formOptions,
                        'actions' => $createActions,
                        'tabs' => $tabs,
                    ],
                ],
            ],
        ];

        if ($factoryMethod) {
            $data['enhavo_resource']['inputs'][$resourceName]['factory_method'] = $factoryMethod;
        }

        if ($factoryArguments) {
            $data['enhavo_resource']['inputs'][$resourceName]['factory_arguments'] = $factoryArguments;
        }

        if ($listRoute) {
            $data['enhavo_resource']['grids'][$resourceName]['collection'] = [
                'class' => 'Enhavo\Bundle\ResourceBundle\Collection\ListCollection',
            ];

            if (isset($listRoute['defaults']['_sylius']['sortable'])) {
                $data['enhavo_resource']['grids'][$resourceName]['collection']['sortable'] = !!$listRoute['defaults']['_sylius']['sortable'];
            }

            if (isset($listRoute['defaults']['_sylius']['viewer']['position_property'])) {
                $data['enhavo_resource']['grids'][$resourceName]['collection']['position_property'] = $listRoute['defaults']['_sylius']['viewer']['position_property'];
            }

            if (isset($listRoute['defaults']['_sylius']['viewer']['parent_property'])) {
                $data['enhavo_resource']['grids'][$resourceName]['collection']['parent_property'] = $listRoute['defaults']['_sylius']['viewer']['parent_property'];
            }

            if (isset($listRoute['defaults']['_sylius']['viewer']['children_property'])) {
                $data['enhavo_resource']['grids'][$resourceName]['collection']['children_property'] = $listRoute['defaults']['_sylius']['viewer']['children_property'];
            }
        }

        return $data;
    }

    private function getApiRoutesConfig($resourceName, $duplicate, $preview, $autoComplete)
    {
        $split = explode('.', $resourceName);
        $company = strtolower($split[0]);
        $resource = strtolower($split[1]);

        $routes = [];

        $routes[$company . '_admin_api_' .  $resource . '_index'] = [
            'path' => '/' . $resource . '/index',
            'methods' => ['GET'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_index',
                    'grid' => $resourceName,
                ]
            ],
        ];

        $routes[$company . '_admin_api_' .  $resource . '_list'] = [
            'path' => '/' . $resource . '/list',
            'methods' => ['GET', 'POST'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_list',
                    'grid' => $resourceName,
                ]
            ],
        ];

        $routes[$company . '_admin_api_' .  $resource . '_create'] = [
            'path' => '/' . $resource . '/create',
            'methods' => ['GET', 'POST'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_create',
                    'input' => $resourceName,
                ]
            ],
        ];

        $routes[$company . '_admin_api_' .  $resource . '_update'] = [
            'path' => '/' . $resource . '/update/{id}',
            'methods' => ['GET', 'POST'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_update',
                    'input' => $resourceName,
                ]
            ],
        ];

        $routes[$company . '_admin_api_' .  $resource . '_delete'] = [
            'path' => '/' . $resource . '/delete/{id}',
            'methods' => ['DELETE'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_delete',
                    'input' => $resourceName,
                ]
            ],
        ];

        $routes[$company . '_admin_api_' .  $resource . '_batch'] = [
            'path' => '/' . $resource . '/batch',
            'methods' => ['POST'],
            'defaults' => [
                '_expose' => 'admin_api',
                '_endpoint' => [
                    'type' => 'resource_batch',
                    'grid' => $resourceName,
                ]
            ],
        ];

        if ($duplicate) {
            $routes[$company . '_admin_api_' .  $resource . '_duplicate'] = [
                'path' => '/' . $resource . '/duplicate/{id}',
                'methods' => ['POST'],
                'defaults' => [
                    '_expose' => 'admin_api',
                    '_endpoint' => [
                        'type' => 'resource_duplicate',
                        'input' => $resourceName,
                    ]
                ],
            ];
        }

        if ($preview) {
            $routes[$company . '_admin_api_' . $resource . '_preview'] = [
                'path' => '/' . $resource . '/preview/{id}',
                'methods' => ['POST'],
                'defaults' => [
                    '_expose' => 'admin_api',
                    '_endpoint' => [
                        'type' => 'resource_preview',
                        'input' => $resourceName,
                        'endpoint' => [
                            'type' => 'null',
                            'resource' => 'expr:resource',
                            'preview' => true,
                        ]
                    ]
                ],
            ];
        }


        if ($autoComplete) {
            $endpointConfig = [
                'type' => 'auto_complete',
                'resource' => $resourceName,
                'repository_method' => $autoComplete['defaults']['_config']['repository']['method'] ?? 'findByTerm',
                'choice_label' => $autoComplete['defaults']['_config']['choice_label'] ?? null,
            ];

            if (isset($autoComplete['defaults']['_config']['repository']['arguments'])) {
                $arguments = [];
                foreach ($autoComplete['defaults']['_config']['repository']['arguments'] as $argument) {
                    if ($argument === 'expr:configuration.getSearchTerm()') {
                        $arguments[] = 'expr:request.get("q")';
                    } else if ($argument === 'expr:configuration.getLimit()') {
                        $arguments[] = 'expr:options.limit';
                    } else {
                        $arguments[] = $argument;
                    }
                }
                $endpointConfig['repository_arguments'] = $arguments;
            }

            if (isset($autoComplete['defaults']['_config']['limit'])) {
                $endpointConfig['limit'] = $autoComplete['defaults']['_config']['limit'];
            }

            $routes[$company . '_admin_api_' . $resource . '_auto_complete'] = [
                'path' => '/' . $resource . '/auto-complete',
                'methods' => ['GET'],
                'defaults' => [
                    '_expose' => 'admin_api',
                    '_endpoint' => $endpointConfig
                ],
            ];
        }

        return $routes;
    }

    private function getAdminRoutesConfig($resourceName, $preview)
    {
        $split = explode('.', $resourceName);
        $company = strtolower($split[0]);
        $resource = strtolower($split[1]);

        $routes = [];


        $routes[$company . '_admin_' .  $resource . '_index'] = [
            'path' => '/' . $resource . '/index',
            'defaults' => [
                '_expose' => 'admin',
                '_vue' => [
                    'component' => 'resource-index',
                    'groups' => 'admin',
                    'meta' => [
                        'api' => $company . '_admin_api_' .  $resource . '_index'
                    ],
                ],
                '_endpoint' => [
                    'type' => 'admin',
                ]
            ],
        ];

        $routes[$company . '_admin_' .  $resource . '_create'] = [
            'path' => '/' . $resource . '/create',
            'defaults' => [
                '_expose' => 'admin',
                '_vue' => [
                    'component' => 'resource-input',
                    'groups' => 'admin',
                    'meta' => [
                        'api' => $company . '_admin_api_' .  $resource . '_create'
                    ],
                ],
                '_endpoint' => [
                    'type' => 'admin',
                ]
            ],
        ];

        $routes[$company . '_admin_' .  $resource . '_update'] = [
            'path' => '/' . $resource . '/update/{id}',
            'defaults' => [
                '_expose' => 'admin',
                '_vue' => [
                    'component' => 'resource-input',
                    'groups' => 'admin',
                    'meta' => [
                        'api' => $company . '_admin_api_' .  $resource . '_update'
                    ],
                ],
                '_endpoint' => [
                    'type' => 'admin',
                ]
            ],
        ];

        return $routes;
    }
}

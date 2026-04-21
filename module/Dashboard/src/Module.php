<?php

namespace Dashboard;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Modelo\DAO\DashboardDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\DashboardDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\DashboardController::class => function ($container) {
                    return new Controller\DashboardController($container->get(Modelo\DAO\DashboardDAO::class));
                },
                Controller\VisualizacionController::class => function ($container) {
                    return new Controller\VisualizacionController($container->get(Modelo\DAO\DashboardDAO::class));
                },
            ],
        ];
    }
}

<?php

namespace Reportes;

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
                Modelo\DAO\ReportesDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\ReportesDAO($dbAdapter);
                },
                Modelo\DAO\ProgramacionDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\ProgramacionDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\ReportesController::class => function ($container) {
                    return new Controller\ReportesController($container->get(Modelo\DAO\ReportesDAO::class));
                },
                Controller\ProgramacionController::class => function ($container) {
                    return new Controller\ProgramacionController($container->get(Modelo\DAO\ProgramacionDAO::class));
                },
            ],
        ];
    }
}

<?php

namespace Indicadores;

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
                Modelo\DAO\IndicadoresDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\IndicadoresDAO($dbAdapter);
                },
                Modelo\DAO\ResultadosDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\ResultadosDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\IndicadoresController::class => function ($container) {
                    return new Controller\IndicadoresController($container->get(Modelo\DAO\IndicadoresDAO::class));
                },
                Controller\ResultadosController::class => function ($container) {
                    return new Controller\ResultadosController($container->get(Modelo\DAO\ResultadosDAO::class));
                },
            ],
        ];
    }
}

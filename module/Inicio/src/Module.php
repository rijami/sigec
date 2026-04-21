<?php

namespace Inicio;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface {

    public function getConfig() {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig() {
        return [
            'factories' => [
                Modelo\DAO\InicioDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\InicioDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig() {
        return [
            'factories' => [
                Controller\BandejaController::class => function ($container) {
                    return new Controller\BandejaController($container->get(Modelo\DAO\InicioDAO::class));
                },
            ],
        ];
    }

}

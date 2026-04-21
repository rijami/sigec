<?php

namespace Controlacceso;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface {

    public function getConfig() {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig() {
        return [
            'factories' => [
                Modelo\DAO\RolesDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\RolesDAO($dbAdapter);
                },
                //--------------------------------------------------------------
                Modelo\DAO\RecursoRbacDAO::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    return new Modelo\DAO\RecursoRbacDAO($dbAdapter);
                },
            ],
        ];
    }

    public function getControllerConfig() {
        return [
            'factories' => [
                Controller\RolesController::class => function ($container) {
                    return new Controller\RolesController($container->get(Modelo\DAO\RolesDAO::class));
                },
                //--------------------------------------------------------------
                Controller\RecursosrbacController::class => function ($container) {
                    return new Controller\RecursosrbacController($container->get(Modelo\DAO\RecursoRbacDAO::class));
                },
            ],
        ];
    }
}

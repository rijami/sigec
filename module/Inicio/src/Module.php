<?php

namespace Inicio;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Inicio\Service\OutlookMailService;

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
                OutlookMailService::class => function ($container) {
                    $config = $container->get('config');
                    $mailConfig = $config['mail']['outlook'] ?? [];
                    return new OutlookMailService($mailConfig);
                },
            ],
        ];
    }

    public function getControllerConfig() {
        return [
            'factories' => [
                Controller\BandejaController::class => function ($container) {
                    return new Controller\BandejaController(
                        $container->get(Modelo\DAO\InicioDAO::class),
                        $container->get(OutlookMailService::class)
                    );
                },
            ],
        ];
    }

}

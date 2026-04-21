<?php

namespace Usuarios\Modelo\RBAC;

use Laminas\Authentication\Adapter\DbTable\CallbackCheckAdapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Interop\Container\ContainerInterface;

class IdentityManagerFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): object {
        $adapter = new CallbackCheckAdapter($container->get(AdapterInterface::class));
        $adapter->setTableName('usuario');
        $adapter->setIdentityColumn('login');
        $adapter->setCredentialColumn('password');
        $adapter->setCredentialValidationCallback(function ($hash, $password) {
            return password_verify($password, $hash);
        });
        $RbacDAO = $container->get('RbacDAO');
        return new IdentityManager($adapter, $RbacDAO);
    }
}

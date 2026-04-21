<?php

/**
 * Global Configuration Override
 *
 * Valores leídos desde variables de entorno para que no haya
 * credenciales hardcodeadas en el repositorio.
 * Configurar en Dokploy: DB_HOST, DB_PORT, DB_NAME
 */

$host     = getenv('DB_HOST') ?: '172.17.1.120';
$port     = getenv('DB_PORT') ?: '1433';
$dbname   = getenv('DB_NAME') ?: 'DB_GESTION_INDICADORES';
$servidor = $host . ',' . $port;   // formato aceptado por sqlsrv

return [
    'db' => [
        'driver'         => 'Pdo',
        'dsn'            => 'sqlsrv:Server=' . $servidor . ';Database=' . $dbname,
        'driver_options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ],
    ],
    'session_containers' => [
        Laminas\Session\Container::class,
    ],
    'session_storage' => [
        'type' => Laminas\Session\Storage\SessionArrayStorage::class,
    ],
    'session_config' => [
        'name'                => 'MI_SESSION',
        'use_cookies'         => true,
        'cookie_secure'       => true,   // HTTPS en producción
        'cookie_httponly'     => true,
        'cookie_lifetime'     => 7200,   // 2 horas
        'gc_maxlifetime'      => 7200,
        'cache_expire'        => 120,    // minutos
        'remember_me_seconds' => 7200,
    ],
];

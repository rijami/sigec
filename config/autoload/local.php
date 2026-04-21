<?php

/**
 * Local Configuration Override
 *
 * Credenciales leídas desde variables de entorno.
 * Configurar en Dokploy: DB_USER, DB_PASS
 * No hardcodear contraseñas aquí.
 */

$username = getenv('DB_USER') ?: 'Estadistica';
$password = getenv('DB_PASS') ?: '';

return [
    'db' => [
        'username' => $username,
        'password' => $password,
        'adapters' => [
            'DB_MARCACIONES' => [
                'username' => $username,
                'password' => $password,
            ],
        ],
    ],
];

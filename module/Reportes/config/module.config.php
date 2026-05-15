<?php

namespace Reportes;

return [

    'router' => [
        'routes' => [
            'reportes' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/reportes',
                    'defaults' => [
                        'controller' => Controller\ReportesController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'reportes' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/reportes/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ReportesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'programacion' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/programacion/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\ProgramacionController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'Reportes' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];

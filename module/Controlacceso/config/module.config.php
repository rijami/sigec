<?php

namespace Controlacceso;

return [
    'router' => [
        'routes' => [
            'controlacceso' => [
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/controlacceso',
                    'defaults' => [
                        'controller' => Controller\RolesController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'roles' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/roles/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\RolesController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'recursosrbac' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/recursosrbac/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\RecursosrbacController::class,
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
            'controlacceso' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];

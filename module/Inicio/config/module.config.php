<?php

namespace Inicio;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'inicio' => [
                // First we define the basic options for the parent route:
                'type' => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route' => '/inicio',
                    'defaults' => [
                        'controller' => Controller\BandejaController::class,
                        'action' => 'index',
                    ],
                ],
                // The following allows "/news" to match on its own if no child
                // routes match:
                'may_terminate' => true,
                // Child routes begin:
                'child_routes' => [
                    'bandeja' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/bandeja/:action[/:id1]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id1' => '[a-zA-Z0-9_-]*',
                            ),
                            'defaults' => [
                                'controller' => Controller\BandejaController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
//            'inicio1' => [
//                'type' => \Laminas\Router\Http\Literal::class,
//                'options' => [
//                    'route' => '/inicio1',
//                    'defaults' => [
//                        'controller' => Controller\BandejaController::class,
//                        'action' => 'index',
//                    ],
//                ],
//                'may_terminate' => false,
//                'child_routes' => [
//                    'bandeja' => [
//                        'type' => \Laminas\Router\Http\Segment::class,
//                        'options' => [
//                            'route' => '/bandeja/:action[/:fechaultingreso]',
//                            'constraints' => array(
//                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                                'fechaultingreso' => '[0-9%:-]*',
//                            ),
//                            'defaults' => [
//                                'controller' => Controller\BandejaController::class,
//                                'action' => 'index',
//                            ],
//                        ],
//                    ],
//                ],
//            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'inicio' => __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ]
    ],
];

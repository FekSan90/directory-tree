<?php

namespace DocumentManager;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Controller\DocumentManagerController::class => function ($container) {
                return new Controller\DocumentManagerController($container);
            }
        ],
    ],

    'router' => [
        'routes' => [
            'DocumentManager' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/DocumentManager[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\DocumentManagerController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'doctrine' => [
        'driver' => [
            'my_annotation_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    'module/DocumentManager/src/Model',
                ],
            ],

            'orm_default' => [
                'drivers' => [
                    'DocumentManager\Model' => 'my_annotation_driver',
                ],
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'documentmanager' => __DIR__ . '/../view',
        ],
        'strategies' => array('ViewJsonStrategy',),
    ],
];
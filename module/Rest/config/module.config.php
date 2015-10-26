<?php

return array(
     'controllers' => array(
         'invokables' => array(
             'Rest\Controller\Rest' => 'Rest\Controller\RestController',
         ),
     ),
    'router' => array(
         'routes' => array(
             'rest' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/rest/:collection[/:resource][/:type]',
                     'constraints' => array(
                         'collection' => '(cars)',
                         'resource' => '[0-9]+',
                         'type' => '(json|xml)',
                     ),
                     'defaults' => array(
                         'controller' => 'Rest\Controller\Rest',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),
     'view_manager' => array(
         'template_path_stack' => array(
             'rest' => __DIR__ . '/../view',
         ),
     ),
 );

?>
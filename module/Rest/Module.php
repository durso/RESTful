<?php

namespace Rest;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway as gateway;
use Rest\Model\Cars;
use Rest\Model\CarsTable;
use Rest\Model\Auth;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface{
    public function getAutoloaderConfig(){
         return array(
             'Zend\Loader\ClassMapAutoloader' => array(
                 __DIR__ . '/autoload_classmap.php',
             ),
             'Zend\Loader\StandardAutoloader' => array(
                 'namespaces' => array(
                     __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                 ),
             ),
         );
    }

    public function getConfig(){
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig(){
        return array(
            'factories' => array(
                'Rest\Model\CarsTable' =>  function($sm) {
                    $gateway = $sm->get('CarsTableGateway');
                    $table = new CarsTable($gateway);
                    return $table;
                },
                'CarsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Cars());
                    return new gateway('cars', $dbAdapter, null, $resultSetPrototype);
                },
                'Rest\Model\Auth' =>  function() {
                    $auth = new Auth;
                    return $auth;
                },
            ),
        );
    }
}
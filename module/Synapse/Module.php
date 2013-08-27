<?php

namespace Synapse;

use Synapse\Model\Table;
use Synapse\Authentication\Storage\AuthStorage;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\InitProviderInterface;

use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Db\TableGateway\Feature;

use Zend\EventManager\StaticEventManager;

use Zend\ModuleManager\ModuleManagerInterface as ModuleManager;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;

class Module implements 
    AutoloaderProviderInterface, 
    ServiceProviderInterface,
    ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ClietsDbAdapter' => function($sm) {
                    $config = $sm->get('config');
                    $config = $config['db_adapter_manager']['synapse']['sipmlestream_clients'];
                    $dbAdapter = new DbAdapter($config);
                    return $dbAdapter;
                },
                'CatchupDbAdapter' => function($sm) {
                    $config = $sm->get('config');
                    $config = $config['db_adapter_manager']['synapse']['catchuptv'];
                    $dbAdapter = new DbAdapter($config);
                    return $dbAdapter;
                },
                'CustomersDbAdapter' => function($sm) {
                    $config = $sm->get('config');
                    $config = $config['db_adapter_manager']['synapse']['customers'];
                    $dbAdapter = new DbAdapter($config);
                    return $dbAdapter;
                },
                'CustomerTable' => function($sm) {
                    return new Table\CustomerTable('customer', $sm->get('CustomersDbAdapter'));
                },
                'AuditTable' => function($sm) {
                    return new Table\AuditTable('audit', $sm->get('CustomersDbAdapter'));
                },
                'PackageTable' => function($sm) {
                    return new Table\PackageTable('package', $sm->get('CustomersDbAdapter'));
                },
                'DeviceTable' => function($sm) {
                    return new Table\DeviceTable('device', $sm->get('CustomersDbAdapter'));
                },
                'PaymentTable' => function($sm) {
                    return new Table\PaymentTable('package', $sm->get('CustomersDbAdapter'));
                },
                'AuthStorage' => function($sm) {
                    return new AuthStorage('synapse');
                },
                'AuthService' => function($sm) {
                    $authAdapter = new AuthAdapter($sm->get('CustomersDbAdapter'), 
                        'customer', 'customer_activation_code', 
                        'customer_pin'
                    );

                    $authService = new AuthenticationService();
                    $authService->setAdapter($authAdapter);
                    $authService->setStorage($sm->get('AuthStorage'));

                    return $authService;
                }
            ),
            'aliases' => array(
                'CustomerModel' => 'CustomerTable',
                'AuditModel' => 'AuditTable',
                'PackageModel' => 'PackageTable',
                'PaymentModel' => 'PaymentTable',
                'DeviceModel' => 'DeviceTable',
            ),
        );
    }
}

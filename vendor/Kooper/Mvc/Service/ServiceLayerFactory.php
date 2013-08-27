<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
namespace Kooper\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

/**
 * Service factory provides an easy interface to configure a service layer with
 * common features such as mappers, models, tables and resultsets
 * 
 * Example config:
 * <code>
 * 'service_manager' => array(
 *     'factories' => array(
 *         'service\CdnService' => 'Kooper\Mvc\Service\ServiceLayerFactory',
 *     ),
 * ),
 * 'service_layer' => array(
 *     'service\CdnService' => array(
 *         'service' => 'Lingard\Service\CdnService',
 *         'mappers' => array(
 *             'cdn' => array(
 *                 'name' => 'Lingard\Model\Mapper\CdnMapper',
 *                 'tablename' => 'cdns',
 *                 'entity' => 'cdn',
 *             )
 *         ),
 *         'entities' => array(
 *             'cdn' => 'Lingard\Model\Cdn',
 *         ),
 *     ),
 * ),
 * </code>
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
class ServiceLayerFactory
    implements FactoryInterface
{
    /**
     * Array of entities available for use with the service layer
     * The array key is the alias mapped
     * 
     * @var array
     */
    protected $_entities;
    
    /**
     * Service layer configuration
     * 
     * @var array
     */
    protected $_config;

    /**
     * Add a service instance to the ServiceManager based on the configuration
     * defined in the service_layer namespace
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return Kooper\Service\AbstractService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $canonicalName = func_get_arg(2);
        $config = $serviceLocator->get('Config');
        $slConfig = isset($config['service_layer']) ? $config['service_layer'] : array();
        
        $this->_config = $slConfig;
        
        return $this->loadService($canonicalName, $serviceLocator);
    }
    
    /**
     * Load a service based on the given configuration
     * 
     * @param string $canonicalName
     * @param ServiceLocatorInterface $serviceLocator
     * @return \Kooper\Mvc\Service\AbstractService
     */
    public function loadService($canonicalName, ServiceLocatorInterface $serviceLocator)
    {
        if (!isset($this->_config[$canonicalName])) {
            return;
        }
        
        $serviceConfig = $this->_config[$canonicalName];
        
        if (!is_array($serviceConfig)) {
            $serviceName = $serviceConfig;
        } else if (is_array($serviceConfig) && isset($serviceConfig['service'])) {
            $serviceName = $serviceConfig['service'];
        }
        
        $options = array("user" => $serviceLocator->get('User'));
        
        if (is_array($serviceConfig)) {
            if (isset($serviceConfig['entities'])
                && is_array($serviceConfig['entities'])
            ) {
                $entities = array();
                foreach ($serviceConfig['entities'] as $alias => $entityConfig) {
                    if (!is_array($entityConfig)) {
                        $entityConfig = array($entityConfig);
                    }
                    
                    if (null !== ($entity = $this->loadEntity($entityConfig, $serviceLocator))) {
                        $entities[$alias] = $entity;
                    }
                }
                $this->_entities = $entities;
                
                $options['models'] = $entities;
            }

            if (isset($serviceConfig['mappers'])
                && is_array($serviceConfig['mappers']
            )) {
                $mappers = array();
                foreach ($serviceConfig['mappers'] as $alias => $mapperConfig) {
                    if (null !== ($mapper = $this->loadMapper($mapperConfig, $serviceLocator))) {
                        $mappers[$alias] = $mapper;
                    }
                }
                
                $options['mappers'] = $mappers;
            }
            
            if (isset($serviceConfig['services'])
                && is_array($serviceConfig['services']
            )) {
                $services = array();
                foreach ($serviceConfig['services'] as $alias => $subCanonicalName) {
                    if (null !== ($service = $this->loadService($subCanonicalName, $serviceLocator))) {
                        $services[$alias] = $service;
                    }
                }
                
                $options['services'] = $services;
            }
        }

        return new $serviceName($options);
    }
    
    /**
     * Load an entity based on the given configuration
     * 
     * Example configuration:
     * <code>
     *  array('name' => 'Application\Model\EntityA')
     *  // or 
     *  array('Application\Model\EntityA')
     * </code>
     * 
     * @param array $entityConfig
     * @param ServiceLocatorInterface $serviceLocator
     * @return Kooper\Model\Mapper\AbstractMapper
     */
    public function loadEntity(array $entityConfig, ServiceLocatorInterface $serviceLocator)
    {
        if (isset($entityConfig['name'])) {
            $entityName = $entityConfig['name'];
        } else if (is_numeric(key($entityConfig))
                   && is_string(current($entityConfig))
        ) {
            $entityName = current($entityConfig);
        } else {
            return;
        }
        
        return new $entityName;
    }
    
    /**
     * Load a mapper instance based on the given configuration
     * 
     * Example configuration:
     * <code>
     *  array(
     *      'name' => 'Application\Model\Mapper\ModelMapper',
     *      'tablename' => 'table_a',
     *      'entity' => 'entity_alias',
     *  )
     * 
     * // or without a DB
     *  array('Application\Model\Mapper\ModelMapper')
     * </code>
     * 
     * @param array $mapperConfig
     * @param ServiceLocatorInterface $serviceLocator
     * @return Kooper\Model\Mapper\AbstractMapper
     */
    public function loadMapper(array $mapperConfig, ServiceLocatorInterface $serviceLocator)
    {
        $tableGateway = null;
        
        if (isset($mapperConfig['name'])) {
            $mapperName = $mapperConfig['name'];
        } else if (is_numeric(key($mapperConfig))
                   && is_string(current($mapperConfig))
        ) {
            $mapperName = current($mapperConfig);
        } else {
            return;
        }
        
        if (isset($mapperConfig['tablename'])) {
            $dbAdapter = $serviceLocator->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new ResultSet();
        
            if (isset($mapperConfig['entity'])
                && null !== ($entity = $this->_getEntity($mapperConfig['entity']))
            ) {
                $resultSetPrototype->setArrayObjectPrototype($entity);
            }
        
            $tableGateway = new TableGateway($mapperConfig['tablename'], $dbAdapter, null, $resultSetPrototype);
        }
        
        return new $mapperName($tableGateway);
    }
    
    /**
     * Get a registered entity instance by the given alias
     * 
     * @param string $entityAlias
     * @return \ArrayObject
     */
    protected function _getEntity($entityAlias)
    {
        if (is_array($this->_entities)
            && isset($this->_entities[$entityAlias])
        ) {
            return $this->_entities[$entityAlias];
        }
    }
}
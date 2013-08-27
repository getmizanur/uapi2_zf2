<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Service
 */
namespace Kooper\Service;

use Kooper\User\User;
use Kooper\Model\AbstractEntity;
use Kooper\Model\Mapper\AbstractMapper;

/**
 * Abstract service layer
 * 
 * Provides common functionality for all service layers such as:
 * - setting and getting mappers
 * - organisation request validation
 * - setting and getting of the common user object
 * - query string consolidation such as filter options, annotaion options,
 *   ordering and pagination
 * 
 * @category   Kooper
 * @package    Kooper_Service
 */
abstract class AbstractService
{
    /**
     * User instance
     * 
     * @var Kooper\User\User
     */
    protected $_user;
    
    /**
     * Array of AbstractMapper instances
     * 
     * @var array
     */
    protected $_mappers = array();
    
    /**
     * Array of AbstractEntity instances
     * 
     * @var array
     */
    protected $_models = array();
    
    /**
     * Array of AbstractService instances
     * 
     * @var array
     */
    protected $_services = array();
    
    /**
     * Bool flag to state if the organisation should be checked and filtered
     * when requesting the filter config
     * 
     * @var bool
     */
    protected $_ignoreOrganisationFiltering = false;
    
    /**
     * Prepare the service layer
     * 
     * Accept an array of options proxying to _set$Key($value)
     * e.g array("kung_foo" => "bar"); proxies to _setKungFoo("bar");
     * 
     * @param array $options
     * @return void
     */
    public function __construct(array $options = null)
    {
        foreach ($options as $key => $value) {
            $method = "_set" . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    
    /**
     * Set the user object
     * 
     * @param User $user
     * @return AbstractService
     */
    protected function _setUser(User $user)
    {
        $this->_user = $user;
        return $this;
    }
    
    /**
     * Get the user
     * 
     * @return User
     * @throws \Exception
     */
    protected function _getUser()
    {
        if (!$this->_user) {
            throw new \Exception("No user defined");
        }
        return $this->_user;
    }
    
    /**
     * Set the an array of mapper instances where the array key is the alias of
     * the mapper and the value is the instance itself
     * 
     * @param array $mappers
     * @return AbstractService
     */
    protected function _setMappers(array $mappers)
    {
        $this->_mappers = $mappers;
        return $this;
    }
    
    /**
     * Get a registered mapper instance based on the given alias
     * 
     * @param string $alias
     * @return AbstractMapper
     * @throws \Exception
     */
    protected function _getMapper($alias = 'livestream')
    {
        if (!isset($this->_mappers[$alias])) {
            throw new \Exception("No $alias mapper defined");
        }
        return $this->_mappers[$alias];
    }
    
    /**
     * Set the an array of AbstractEntity instances where the array key is the
     * alias of the entity and the value is the instance itself
     * 
     * @param array $models
     * @return AbstractService
     */
    protected function _setModels(array $models)
    {
        $this->_models = $models;
        return $this;
    }
    
    /**
     * Get a registered entity instance based on the given alias
     * 
     * @param string $alias
     * @return AbstractEntity
     * @throws \Exception
     */
    protected function _getModel($alias)
    {
        if (!isset($this->_models[$alias])) {
            throw new \Exception("No $alias model defined");
        }
        return $this->_models[$alias];
    }
    
    /**
     * Set the an array of AbstractService instances where the array key is the
     * alias of the service and the value is the instance itself
     * 
     * @param array $services
     * @return AbstractService
     */
    protected function _setServices(array $services)
    {
        $this->_services = $services;
        return $this;
    }
    
    /**
     * Get a registered service instance based on the given alias
     * 
     * @param string $alias
     * @return AbstractService
     * @throws \Exception
     */
    protected function _getService($alias)
    {
        if (!isset($this->_services[$alias])) {
            throw new \Exception("No $alias service defined");
        }
        return $this->_services[$alias];
    }
    
    /**
     * Validates the organisation id(s) against the id's the user has access to
     * 
     * @param string|array $organisationIds
     * @return boolean
     * @throws \InvalidArgumentException
     */
    protected function _validateOrganisationId($organisationIds)
    {
        $organisations = $this->_getUser()->getOrganisations();
        
        if (!is_string($organisationIds) && !is_array($organisationIds)) {
            throw new \InvalidArgumentException("Cannot validate given organisation id. Must be wither a comma separated string or an array", 500);
        }
        
        if (false === strpos($organisationIds, ',')) {
            if (array_key_exists($organisationIds, $organisations)) {
                return true;
            }
            throw new \InvalidArgumentException("Given organisation id is not supported", 400);
        }
        
        $organisationIds = explode(',', $organisationIds);
        $unsupported = array_diff_key(array_flip($organisationIds), $organisations);
        
        if ($unsupported) {
            throw new \InvalidArgumentException(sprintf("The following given organisation id's are not supported: %s", implode(', ', array_flip($unsupported))), 400);
        }
        
        return true;
    }
    
    /**
     * Check the options for any pagination flags and assign them to the mapper
     * if present
     * 
     * You may also pass a flag to force the paginator with the defaults if no
     * paginator was set
     * 
     * @param AbstractMapper $mapper
     * @param array $options
     * @param boolean $force TRUE to force pagination
     * @return array|boolean Given config of paginator if set, otherwise, FALSE
     */
    protected function _preparePagination(AbstractMapper $mapper, array $options = null, $force = false)
    {
        $paginationKeys = array('limit' => null, 'page' => null);
        $paginationConfig = array_intersect_key($options, $paginationKeys);
        
        if ($paginationConfig) {
            $mapper->setPaginator($paginationConfig);
            return $paginationConfig;
        } else if (true === $force) {
            $mapper->setPaginator();
        }
        
        return false;
    }
    
    /**
     * Get the order config from the given options
     * 
     * @param array $options
     * @return array|null
     */
    public function _getOrderOptions(array $options = null)
    {       
        if (isset($options['order']) && is_string($options['order'])) {
            $orderParts = explode(':', $options['order']);
            $order = array(
                    'property' => $orderParts[0],
                    'direction' => isset($orderParts[1]) ? strtolower($orderParts[1]) : 'asc',
                );
            
            return $order;
        }
    }
    
    /**
     * Find all annotation configs from the given options and convert them into
     * an array
     * 
     * e.g $_GET[foo_fields] = 'id,alias,name' will result in:
     * array('foo' => array('id', 'alias', 'name'))
     * 
     * @param array $options
     * @return array|null
     */
    protected function _prepareModelAnnotations(array $options = null)
    {
        if (!$options) {
            return;
        }
        
        $annotations = array();
        foreach ($options as $key => $value) {
            if (false !== ($model = strstr($key, '_fields', true))) {
                $annotations[$model] = array_map('trim', explode(',', $value));
            }
        }
        
        if ($annotations) {
            return $annotations;
        }
    }
    
    /**
     * Prepare the filter configuration options, validate the given organisations
     * and force them if not set
     * 
     * @param array $options
     * @param bool $ignoreOrganisation
     * @return array
     */
    protected function _prepareFilterConfig(array $options = null, $ignoreOrganisation = false)
    {
        $config = array();
        if (isset($options['filter'])) {
            $config = $options['filter'];
        }
        
        if (false === $ignoreOrganisation && false === $this->_ignoreOrganisationFiltering) {
            if (isset($config['organisation_id'])) {
                $this->_validateOrganisationId($config['organisation_id']);
            } else {
                $organisations = $this->_getUser()->getOrganisations();
                $config['organisation_id'] = array_keys($organisations);
            }
        }
        
        return $config;
    }
    
    /**
     * Prepare the given set of options for a nested object
     * 
     * @param array|null $options
     * @return array
     */
    protected function _prepareNestedOptions($options = null)
    {
        if (!is_array($options)) {
            $options = array();
        }
        
        if (!isset($options['filter'])) {
            $options['filter'] = array();
        }
        
        if (isset($options['order'])) {
            unset($options['order']);
        }
        
        if (isset($options['page'])) {
            unset($options['page']);
        }
        
        if (isset($options['limit'])) {
            unset($options['limit']);
        }
        
        return $options;
    }
}
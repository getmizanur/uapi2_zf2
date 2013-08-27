<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Model
 * @subpackage Mappers
 */
namespace Kooper\Model\Mapper;

use Kooper\Model\AbstractEntity;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Paginator;

/**
 * Abstract mapper object providing commonly used functionality to interact with
 * the table gateway
 * 
 * This abstract mapper shuld be used with all mappers interacting with the database
 * All mappers that do not need strong cases defining why in the file doc blocks
 * 
 * - Utilises a rudamentary ORM system to map entity properties to database columns.
 * - Minimises the potential issue of multiple joins, and column inclusions of the
 *   same table data in a query allowing for more widespread juggling of the select object
 * - Assists with common functionality such as basic selecting and updating,
 *   pagination management, annotations ordering and filtering
 * 
 * @category   Kooper
 * @package    Kooper_Model
 * @subpackage Mappers
 */
abstract class AbstractMapper
{
    /**
     * Default pagination config
     */
    const PAGINATOR_DEFAULT_LIMIT = 20;
    const PAGINATOR_MAX_LIMIT = 100;
    const PAGINATOR_DEFAULT_PAGE = 1;
    
    /**
     * Namespace to fetch the default properties associated to the table
     */
    const COLUMN_DEFAULTS = "_defaults";
    
    /**
     * Namespace to fetch all the properties associated to the table
     */
    const COLUMN_FULL = "_full";
 
    /**
     * The default join spec key
     * 
     * @var string
     */
    protected $_defaultJoinSpecKey;
    
    /**
     * Database table gateway
     * 
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $_tableGateway;

    /**
     * Relational table join specifications, available columns and default criteria
     * Used with self::_join() and self::_columns()
     * 
     * Each spec is typically a database table with configuration data to state
     * how the table joins with others, default columns to load and the relationship
     * between the resultsets prototype model
     * 
     * Join specs are defined as an associative array.
     * The array key is the name of the annotation group which the resultsets
     * model will utilise:
     * <code>
     * 'format' => array(...)
     * </code>
     * 
     * The following configuration options are supported for each block:
     * @spec string table_name  The database table name
     * @spec string dependency  Array key of the join spec this requires to allow
     *                          the properties to be fetched in the SQL query
     * @spec array  join_spec   Specification stating how the table joins onto
     *                          the default. Proxies to Zend\Db\Select::join()
     * @spec array  properties  Associative array of column to model properties
     *                          where the array key is the COLLUMN and the value
     *                          is the PROPERTY
     * @spec array annotation_alias Array of aliases which when present, map to
     *                          another annotation spec. Typically used for
     *                          nested models
     * @spec array defaults     Array spec of defaults
     * 
     * @defaultspecs bool|string prefix Prefix all columns within this spec with
     *                          the given string value. If a TRUE flag is given,
     *                          the join specs key name is used.
     * @defaultspecs array force_properties List of properties to forcably include,
     *                          requested or not when the spec is in use
     * @defaultspecs bool force_join    Flag to state if the spec should be auto
     *                          joined if any of the columns are requested as
     *                          dependancies
     * @defaultspecs array properties   Array of default properties to load when
     *                          the spec is requested and the self::COLUMN_DEFAULTS
     *                          value is given
     * 
     * @var array
     */
    protected $_joinSpecs = array();
    
    /**
     * Pagination configuration
     * 
     * @var array|null
     */
    protected $_pagniatorConfig;
    
    /**
     * Constructor
     * 
     * @param \Zend\Db\TableGateway\TableGateway $gateway
     * @return void
     */
    public function __construct(TableGateway $gateway = null)
    {
        $this->setTableGateway($gateway);
    }

    /**
     * Set the table gateway
     * 
     * @param \Zend\Db\TableGateway\TableGateway $gateway
     * @return \Kooper\Model\Mapper\AbstractMapper
     */
    public function setTableGateway(TableGateway $gateway)
    {
        $this->_tableGateway = $gateway;
        return $this;
    }
    
    /**
     * Get the table gateway
     * 
     * @return \Zend\Db\TableGateway\TableGateway
     */
    public function getTableGateway()
    {
        return $this->_tableGateway;
    }
    
    /**
     * So the potentially configured paginator can be wrpped around the select
     * query, the primary Select is done from here
     *
     * @param Where|\Closure|string|array $where
     * @return ResultSet
     * @throws \Exception Previous message is thrown to display the mysql error
     * @todo The previous exception management shoul dbe done up the chain. Not here
     *       Fix the Exception strategies to include this for all response types
     */
    public function select($where = null)
    {
        $gateway = $this->getTableGateway();
        $gateway->initialize();

        $select = $gateway->getSql()->select();

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        if ($this->hasPaginator()) {
            return $this->_wrapPaginator($select);
        }

        return $gateway->selectWith($select);
    }
    
    /**
     * Delete any records from the registered database table associated to the
     * given id
     * 
     * @param int $id
     * @return int
     */
    public function delete($id)
    {
        return $this->getTableGateway()->delete(array('id' => $id));
    }
    
    /**
     * Save the given entity to the database
     * 
     * Proxies to either create or update if an id is present in the given entity
     * 
     * @param \Kooper\Model\AbstractEntity $entity
     * @return mixed
     */
    public function save(AbstractEntity $entity)
    {
        return $entity->id ? $this->update($entity) : $this->insert($entity);
    }
    
    /**
     * Update the given entity object in the database
     * 
     * @param AbstractEntity $entity
     * @return int
     * @throws \Exception
     */
    public function update(AbstractEntity $entity)
    {
        $data = $this->_getPropertyColumnData($entity);
        
        if ($data) {
            if (isset($data['id'])) {
                unset($data['id']);
            }
            return $this->getTableGateway()->update($data, array('id' => $entity->id));
        }
        
        throw new \Exception("No rows affected");
    }
    
    /**
     * Create the given entity object in the database
     * 
     * @param AbstractEntity $entity
     * @return int
     * @throws \Exception
     */
    public function create(AbstractEntity $entity)
    {
        $data = $this->_getPropertyColumnData($entity);
        
        if ($data) {
            $gateway = $this->getTableGateway();

            if (null != ($rowsAffected = $gateway->insert($data))) {
                $entity->id = $gateway->getLastInsertValue();
                return $entity;
            }
        }
        
        throw new \Exception("No rows affected");
    }
    
    /**
     * Get the given entities data mapped to the associating database column
     * 
     * @param AbstractEntity $entity
     * @return array
     */
    protected function _getPropertyColumnData(AbstractEntity $entity)
    {
        if ($this->_defaultJoinSpecKey
            && isset($this->_joinSpecs[$this->_defaultJoinSpecKey])
        ) {
            $joinSpec = $this->_joinSpecs[$this->_defaultJoinSpecKey];
        } else if (is_array($this->_joinSpecs)) {
            $joinSpec = current($this->_joinSpecs);
        }
        
        if (!($joinSpec
              && is_array($joinSpec)
              && isset($joinSpec['properties'])
        )) {
            return array();
        }
        
        $properties = $joinSpec['properties'];
        
        $data = array();
        foreach ($properties as $key => $property) {
            $columnName = is_numeric($key) ? $property : $key;
            $data[$columnName] = $entity->get($property);
        }
        
        return $data;
    }
    
    /**
     * Set the pagination page and limit specs
     * If both are null, the default constants are used
     * 
     * @param int $page
     * @param int $limit
     * @return \Kooper\Model\Mapper\AbstractMapper
     */
    public function setPaginator($page = null, $limit = null)
    {
        if (is_array($page)) {
            if (isset($page['limit'])) {
                $limit = $page['limit'];
            }
            
            if (isset($page['page'])) {
                $page = $page['page'];
            }
        }
        
        if (!is_numeric($limit)) {
            $limit = self::PAGINATOR_DEFAULT_LIMIT;
        }
        if (!$limit > self::PAGINATOR_MAX_LIMIT) {
            $limit = self::PAGINATOR_MAX_LIMIT;
        }
        if (!is_numeric($page)) {
            $page = self::PAGINATOR_DEFAULT_PAGE;
        }
        
        $this->_pagniatorConfig = array('limit' => $limit, 'page' => $page);
        
        return $this;
    }
    
    /**
     * Check if the mapper has registered paginator settings
     * 
     * @return bool
     */
    public function hasPaginator()
    {
        return ($this->_pagniatorConfig);
    }
    
    /**
     * Accept the given select statement and add it to an instance of Paginator
     * 
     * @param Select $select
     * @return \Kooper\Model\Mapper\Paginator\Paginator
     */
    protected function _wrapPaginator(Select $select)
    {
        if (!$this->hasPaginator()) {
            return;
        }
        
        $gateway = $this->getTableGateway();
        $adapter = $gateway->getAdapter();
        $paginator = new Paginator\Paginator(new Paginator\Adapter\DbSelect($select, $adapter, $gateway->getResultSetPrototype()));
        $paginator->setCurrentPageNumber($this->_pagniatorConfig['page'])
                  ->setItemCountPerPage($this->_pagniatorConfig['limit']);
        
        return $paginator;
    }
    
    /**
     * Set the ordering of the query
     * 
     * @param \Zend\Db\Sql\Select $select
     * @param string|array $order
     * @return \Kooper\Model\Mapper\AbstractMapper
     * @throws \Exception
     */
    protected function _order(Select $select, $order)
    {
        $sql = null;
                
        if (is_array($order)) {
            if (isset($order['property'])) {
                if (!array_key_exists($order['property'], $select->getRawState(Select::COLUMNS))) {
                    throw new \Exception(sprintf("Cannot order by %s. Property is undefined", $order['property']));
                }
                $sql = $order['property'];
            } else {
                return;
            }

            if (isset($order['direction'])) {
                $sql .= " " . $order['direction'];
            }
        } else if (is_string($order)) {
            $sql = $order;
        }

        if ($sql) {
            $select->order($sql);
        }
        
        return $this;
    }
    
    /**
     * To ensure we dont get multiple joins in our quest to compile a usable SQL
     * query, add joins to the select statement using this.
     * 
     * It queries the the $_joinSpec[$modelAlias]['join_spec'] against the registered alias
     * name. Once joined, the join spec is removed from the config as it should
     * not be used again
     * 
     * Should the join rely on another joined table, you may specify the dependant
     * table in the $_joinSpec via the use of the 'dependency' keyword
     * e.g $_joinSpec[$modelAlias]['dependency'] = 'table_b'
     * 
     * If you would like to add columns to the join, use self::_columns()
     * 
     * @param Select $select
     * @param string $modelAlias
     * @param array|string $columns
     * @return \Kooper\Model\Mapper\AbstractMapper
     */
    protected function _join(Select $select, $modelAlias, $columns = null)
    {
        if (isset($this->_joinSpecs[$modelAlias])
            && isset($this->_joinSpecs[$modelAlias]['join_spec'])
        ) {
            if (isset($this->_joinSpecs[$modelAlias]['dependency'])) {
                $this->_join($select, $this->_joinSpecs[$modelAlias]['dependency']);
            }
            
            $joinSpec = $this->_joinSpecs[$modelAlias]['join_spec'];
            $joinType = isset($joinSpec[2]) ? $joinSpec[2] : Select::JOIN_INNER;
            $select->join($joinSpec[0], $joinSpec[1], array(), $joinType);
            unset($this->_joinSpecs[$modelAlias]['join_spec']);
        }
        
        if (null !== $columns) {
            $this->_columns($select, $modelAlias, $columns);
        }
        
        return $this;
    }
    
    /**
     * Normalize the given properties and replace any magic keywords - such as
     * self::COLUMN_DEFAULTS into the relevant property names
     * 
     * @param string $modelAlias
     * @param string|array $properties
     * @return array|void
     */
    protected function _normalizeModelProperties($modelAlias = null, $properties = self::COLUMN_DEFAULTS)
    {
        if (!(isset($this->_joinSpecs[$modelAlias])
              && isset($this->_joinSpecs[$modelAlias]['properties']))
        ) {
            return;
        }
        
        $propertySpec = $this->_joinSpecs[$modelAlias]['properties'];
        $defaultsSpec = isset($this->_joinSpecs[$modelAlias]['defaults']) ? $this->_joinSpecs[$modelAlias]['defaults'] : array();

        if (!is_array($properties)) {
            $properties = array($properties);
        }

        if (false !== ($key = array_search(self::COLUMN_DEFAULTS, $properties))) {
            unset($properties[$key]);

            $properties = array_unique(array_merge($properties, isset($defaultsSpec['properties']) ? $defaultsSpec['properties'] : $propertySpec));
        }

        if (false !== ($key = array_search(self::COLUMN_FULL, $properties))) {
            unset($properties[$key]);
            $properties = array_merge($properties, $propertySpec);
        }

        if (is_array($properties)) {
            $properties = array_intersect($propertySpec, $properties);
            return $properties;
        }
    }
    
    /**
     * Assign a series of columns mapped to model properties to the given Select
     * object ensuring the given properties are supported and havent already been
     * added by comparing the spec against the $_joinSpec[$modelAlias]['properties']
     * 
     * NOTE: In the $_joinSpec[$modelAlias]['properties'], if the column has a
     * different name to the property, the column name is the array key
     * e.g $_joinSpec['tableAlias']['properties']['column_a'] = 'property_a'
     * 
     * If self::COLUMN_DEFAULTS is given, assign the default properties declared in
     * $_joinSpec[$modelAlias]['defaults']['properties']. If this spec doesnt exist
     * in the table alias namespace, use $_joinSpec[$modelAlias]['properties']
     * 
     * NOTE: You may use the constant self::COLUMN_FULL to fetch all the columns
     * associated to the namespace
     * 
     * NOTE: You may also use a combination of constants and column names e.g:
     * array(self::COLUMN_DEFAULTS, 'property_z', 'property_x', 'property_y')
     * 
     * You may also specify a prefix alias. This prefixes the given string alias
     * to each column e.g : table_a.{$prefix_alias}_column_a
     * 
     * If prefix alias is TRUE then the prefix alias becomes the tableAlias
     * If prefix alias is NULL then $_joinSpec[$modelAlias]['defaults']['prefix']
     * is used
     * If prefix alias is FALSE then no prefix alias is used
     * 
     * @param Select $select
     * @param string $modelAlias
     * @param array|string $properties
     * @param bool $forceJoin
     * @param string $prefixAlias
     * @return \Kooper\Model\Mapper\AbstractMapper
     */
    protected function _columns(Select $select, $modelAlias = null, $properties = self::COLUMN_DEFAULTS, $forceJoin = false, $prefixAlias = null)
    {
        $properties = $this->_normalizeModelProperties($modelAlias, $properties);

        if ($properties) {
            if (isset($this->_joinSpecs[$modelAlias]['join_spec'])) {
                if (false === $forceJoin) {
                    return $this;
                }

                $this->_join($select, $modelAlias);
            }

            if (null === $prefixAlias) {
                $defaultsSpec = isset($this->_joinSpecs[$modelAlias]['defaults']) ? $this->_joinSpecs[$modelAlias]['defaults'] : array();
                
                if (isset($defaultsSpec['prefix'])) {
                    $prefixAlias = $defaultsSpec['prefix'];
                    if ($modelAlias !== $defaultsSpec['prefix']
                        && isset($this->_joinSpecs[$prefixAlias])
                        && !(isset($this->_joinSpecs[$prefixAlias]['defaults'])
                             && isset($this->_joinSpecs[$prefixAlias]['defaults']['prefix'])
                             && $this->_joinSpecs[$prefixAlias]['defaults']['prefix'])
                    ) {
                        $prefixAlias = null;
                    }
                }
            }

            if (true === $prefixAlias) {
                $prefixAlias = $modelAlias;
            }

            if (!($prefixAlias && is_string($prefixAlias))) {
                $prefixAlias = null;
            }

            if (isset($this->_joinSpecs[$modelAlias]['table_name'])) {
                $tableName = $this->_joinSpecs[$modelAlias]['table_name'];
            } else {
                $tableName = $modelAlias;
            }

            $newColumns = array();
            foreach ($properties as $key => $propertyName) {
                $columnName = $key;
                if (is_numeric($columnName)) {
                    $columnName = $propertyName;
                }
                $prefixedColumnAlias = $prefixAlias ? "{$prefixAlias}__{$propertyName}" : $propertyName;
                $newColumns[$prefixedColumnAlias] = new Expression("`$tableName`.`$columnName`");
            }

            $existingColumns = $select->getRawState(Select::COLUMNS);
            if (!(is_array($existingColumns)
                  && current($existingColumns) !== Select::SQL_STAR)
            ) {
                $existingColumns = array();
            }

            $select->columns($existingColumns + $newColumns);

            $this->_joinSpecs[$modelAlias]['properties'] = array_diff($this->_joinSpecs[$modelAlias]['properties'], $properties);
        }
        
        return $this;
    }
    
    /**
     * As many options we pass are comma separated for selecting multiple values,
     * Take this, and normalize it into a usable array within the database query
     * 
     * @param mixed $value
     * @return array|string
     */
    protected function _normalizeMultipleMatches($value)
    {
        if (is_string($value) && false !== strpos($value, ',')) {
            $value = explode(',', $value);
        }
        
        if (is_array($value) && count($value) === 1) {
            return current($value);
        }
        
        return $value;
    }
    
    /**
     * Accept the given properties and apply it to all tables assigned in the join spec
     * 
     * @param \Zend\Db\Sql\Select $select
     * @param string|array $annotations
     * @param string $defaultModel
     * @return \Kooper\Model\Mapper\AbstractMapper
     */
    protected function _annotations(Select $select, $annotations = self::COLUMN_DEFAULTS, $defaultModel = null)
    {
        if (is_array($annotations)
            && isset($annotations[$defaultModel])
            && ($annotations[$defaultModel] == self::COLUMN_FULL
                || (is_array($annotations[$defaultModel])
                    && in_array(self::COLUMN_FULL, $annotations[$defaultModel])))
        ) {
            $forceAllModelDefaults = self::COLUMN_DEFAULTS;
        } else {
            $forceAllModelDefaults = null;
        }
        
        if (!is_array($annotations)) {
            $annotations = array($defaultModel => $annotations);
        }
        
        foreach ($this->_joinSpecs as $modelAlias => $spec) {
            $forceJoin = true;
            
            if (isset($spec['defaults'])
                && isset($spec['defaults']['prefix'])
                && is_string($spec['defaults']['prefix'])
                && isset($this->_joinSpecs[$spec['defaults']['prefix']])
                && isset($annotations[$spec['defaults']['prefix']])
            ) {
                if (isset($spec['defaults']['force_join'])) {
                    $forceJoin = (bool)$spec['defaults']['force_join'];
                }
                $annotations[$modelAlias] = $annotations[$spec['defaults']['prefix']];
            }
            
            if (isset($annotations[$modelAlias])) {
                $modelProperties = $annotations[$modelAlias];
                
                if (isset($spec['annotation_alias'])) {
                    $annotationAliases = array_intersect($spec['annotation_alias'], $modelProperties);
                    if ($annotationAliases) {
                        foreach ($annotationAliases as $annotationAliasKey => $annotationAliasValue) {
                            $annotationAlias = is_numeric($annotationAliasKey) ? $annotationAliasValue : $annotationAliasKey;
                            if (isset($this->_joinSpecs[$annotationAlias])
                                && !isset($annotations[$annotationAlias])
                            ) {
                                $this->_columns($select, $annotationAlias, self::COLUMN_DEFAULTS, true);
                            }
                        }
                    }
                }
                
                if (isset($spec['defaults'])
                    && isset($spec['defaults']['force_properties'])
                ) {
                    $modelProperties = array_merge((array)$modelProperties, (array)$spec['defaults']['force_properties']);
                }
            } else {
                $modelProperties = $forceAllModelDefaults;
            }
            
            if ($modelProperties) {
                $this->_columns($select, $modelAlias, $modelProperties, $forceJoin);
            }
        }
        
        return $this;
    }
}
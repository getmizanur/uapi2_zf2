<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link      https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category  Kooper
 * @package   Kooper_Db
 */
namespace Kooper\Db\Sql\Platform\Mysql;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Adapter\StatementContainerInterface;
use Zend\Db\Sql\ExpressionInterface;
use Zend\Db\Sql\Platform\Mysql\SelectDecorator as ZendSelectDecorator;

/**
 * Overrides the Zends Select::processJoins to allow support for multiple
 * database selections and joins within the SQL query
 * 
 * In the default zend method, the select query always added back-ticks
 * to the complete statement of `database.table.column` instead of backticks
 * in each of the . separators.
 * i.e `database`.`table`.`column`
 * 
 * This extension of the SelectDecorator bypasses this.
 * 
 * To utilise, you can inject an instance of this into the Zend\Db\Sql\Sql
 * constructor
 * 
 * @category  Kooper
 * @package   Kooper_Db
 */
class SelectDecorator
    extends ZendSelectDecorator
{    
    /**
     * Overrides the Zends Select::processJoins to allow support for multiple
     * database selections and joins within the SQL query
     * 
     * In the default zend method, the select query always added back-ticks
     * to the complete statement of `database.table.column` instead of backticks
     * in each of the . separators.
     * i.e `database`.`table`.`column`
     * 
     * This extension of the SelectDecorator bypasses this.
     * 
     * To utilise, you can inject an instance of this into the Zend\Db\Sql\Sql
     * constructor
     * 
     * @param \Zend\Db\Adapter\Platform\PlatformInterface $platform
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param \Zend\Db\Adapter\ParameterContainer $parameterContainer
     * @return array|null
     */
    protected function processJoins(PlatformInterface $platform, $adapter = null, ParameterContainer $parameterContainer = null)
    {
        if (!$this->joins) {
            return null;
        }

        // process joins
        $joinSpecArgArray = array();
        foreach ($this->joins as $j => $join) {
            $joinSpecArgArray[$j] = array();
            // type
            $joinSpecArgArray[$j][] = strtoupper($join['type']);
            // table name
            $joinSpecArgArray[$j][] = (is_array($join['name']))
                ? $platform->quoteIdentifierInFragment(current($join['name'])) . ' AS ' . $platform->quoteIdentifierInFragment(key($join['name']))
                : $platform->quoteIdentifierInFragment($join['name']);
            // on expression
            $joinSpecArgArray[$j][] = ($join['on'] instanceof ExpressionInterface)
                ? $this->processExpression($join['on'], $platform, $adapter, $this->processInfo['paramPrefix'] . 'join')
                : $platform->quoteIdentifierInFragment($join['on'], array('=', 'AND', 'OR', '(', ')', 'BETWEEN')); // on
            if ($joinSpecArgArray[$j][2] instanceof StatementContainerInterface) {
                if ($parameterContainer) {
                    $parameterContainer->merge($joinSpecArgArray[$j][2]->getParameterContainer());
                }
                $joinSpecArgArray[$j][2] = $joinSpecArgArray[$j][2]->getSql();
            }
        }

        return array($joinSpecArgArray);
    }
}
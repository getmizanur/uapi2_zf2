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
use Kooper\Mvc\View\RestExceptionStrategy;

/**
 * Rest exception strategy factory configuring a REST exception strategy service
 * 
 * Loads the accept_strategy from the application configuration and depending on
 * the request, if the accept header matches any of the defined values the
 * appropriate view model is used
 * 
 * This allows for thrown exceptions to respond with the correct models based on
 * the accept header type
 * 
 * Example module configuration:
 * <code>
 * 'accept_strategy' => array(
 *     'criteria' => array(
 *         'json' => array(
 *             'accept' => array('application/json'),
 *             'model' => 'Zend\View\Model\JsonModel',
 *         ),
 *         'jsonp' => array(
 *             'accept' => array('application/javascript', 'application/jsonp'),
 *             'model' => 'Zend\View\Model\JsonModel',
 *         ),
 *     ),
 *  ),
 * </code>
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
class RestExceptionStrategyFactory
    implements FactoryInterface
{
    /**
     * Create and return the JSON view strategy
     *
     * Retrieves the ViewJsonRenderer service from the service locator, and
     * injects it into the constructor for the JSON strategy.
     *
     * It then attaches the strategy to the View service, at a priority of 100.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return RestExceptionStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $vmConfig = isset($config['view_manager']) ? $config['view_manager'] : array();
        $rmConfig = isset($config['rest_manager']) ? $config['rest_manager'] : array();
        $asConfig = isset($rmConfig['accept_strategy']) ? $rmConfig['accept_strategy'] : array();
        
        $exceptionStrategy = new RestExceptionStrategy;
        
        $displayExceptions = false;
        $exceptionTemplate = 'error';

        if (isset($vmConfig['display_exceptions'])) {
            $displayExceptions = $vmConfig['display_exceptions'];
        }
        if (isset($vmConfig['exception_template'])) {
            $exceptionTemplate = $vmConfig['exception_template'];
        }

        $exceptionStrategy->setDisplayExceptions($displayExceptions);
        $exceptionStrategy->setExceptionTemplate($exceptionTemplate);
        
        if (isset($asConfig['criteria'])) {
            $spec = array();
            foreach ($asConfig['criteria'] as $name => $criteria) {
                $model = $accept = null;
                
                if (is_array($criteria)) {
                    if (isset($criteria['model'])) {
                        $model = $criteria['model'];
                    }
                    if (isset($criteria['accept'])) {
                        $accept = (array)$criteria['accept'];
                    }
                }
                
                if (!$model) {
                    $model = $this->modelAlias($name);
                }
                
                if (!$accept) {
                    $accept = $this->acceptAlias($name);
                }
                
                if ($model && $accept) {
                    if (isset($spec[$model])) {
                        $spec[$model] = array_merge($spec[$model], $accept);
                    } else {
                        $spec[$model] = $accept;
                    }
                }
            }
            
            $exceptionStrategy->setAcceptHeaderCriteria($spec);
        }
        
        return $exceptionStrategy;
    }
    
    /**
     * Get the default view model to use if none is defined via the configuration
     * 
     * @param string $name
     * @return string
     */
    public function modelAlias($name)
    {
        switch (strtolower($name)) {
            case 'json':
            case 'jsonp':
                return 'Zend\View\Model\JsonModel';
            break;
            case 'feed':
                return 'Zend\View\Model\FeedModel';
            break;
        }
    }
    
    /**
     * Get the default accept alias' to use if none is defined via the configuration
     * 
     * @param string $name
     * @return string
     */
    public function acceptAlias($name)
    {
        switch (strtolower($name)) {
            case 'json':
                return 'application/json';
            break;
            case 'jsonp':
                return array('application/javascript', 'application/jsonp');
            break;
            case 'feed':
                return array('application/rss+xml', 'application/atom+xml');
            break;
        }
    }
}

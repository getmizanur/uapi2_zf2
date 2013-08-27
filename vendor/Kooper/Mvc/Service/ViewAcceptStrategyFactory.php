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
use Zend\Http\Header\Accept\FieldValuePart\AbstractFieldValuePart;
use Kooper\View\Strategy\AcceptStrategy;

/**
 * Create and returns the JSON view strategy
 *
 * Retrieves the ViewJsonRenderer service from the service locator, and
 * injects it into the constructor for the JSON strategy.
 *
 * It then attaches the strategy to the View service, at a priority of 100.
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Service
 */
class ViewAcceptStrategyFactory
    implements FactoryInterface
{
    /**
     * Create and return the accept view strategy commonly used for REST API's
     * based on the accept_strategy module configuration
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return AcceptStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        $rmConfig = isset($config['rest_manager']) ? $config['rest_manager'] : array();
        $asConfig = isset($rmConfig['accept_strategy']) ? $rmConfig['accept_strategy'] : array();
        
        if (isset($asConfig['default_renderer'])) {
            $defaultRenderer = $serviceLocator->get($asConfig['default_renderer']);
        } else {
            $defaultRenderer  = $serviceLocator->get('ViewRenderer');
        }
        
        $acceptStrategy = new AcceptStrategy();
        $acceptStrategy->setDefaultRenderer($defaultRenderer);
        
        if (isset($asConfig['criteria']) && is_array($asConfig['criteria'])) {
            $criteriaConfig = $asConfig['criteria'];
            foreach ($criteriaConfig as $name => $criteria) {
                if (is_numeric($name) && is_string($criteria)) {
                    unset($criteriaConfig[$name]);
                    $name = $criteria;
                    $criteria = null;
                }
                
                if (is_array($criteria)) {
                    if (isset($criteria['renderer'])) {
                        $criteriaConfig[$name]['renderer'] = $serviceLocator->get($criteria['renderer']);
                    } else if (isset($criteria['renderer_alias'])) {
                        $criteriaConfig[$name]['renderer'] = $this->rendererAlias($criteria['renderer_alias'], $serviceLocator);
                    } else {
                        $criteriaConfig[$name]['renderer'] = $this->rendererAlias($name, $serviceLocator);
                    }
                    
                    if (isset($criteria['response'])) {
                        $criteriaConfig[$name]['response'] = $serviceLocator->get($criteria['response']);
                    } else if (isset($criteria['response_alias'])) {
                        $criteriaConfig[$name]['response'] = $this->rendererAlias($criteria['response_alias'], $serviceLocator);
                    } else {
                        $criteriaConfig[$name]['response'] = $this->rendererAlias($name, $serviceLocator);
                    }
                } else if (null !== ($criteria = $this->isAlias($name, $serviceLocator))) {
                    $criteriaConfig[$name] = $criteria;
                }
            }
            
            $acceptStrategy->setAcceptCriteria($criteriaConfig);
        }

        return $acceptStrategy;
    }
    
    /**
     * Get the renderer by alias
     * 
     * @param string $name
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function rendererAlias($name, ServiceLocatorInterface $serviceLocator)
    {
        switch (strtolower($name)) {
            case 'json':
                return $serviceLocator->get('ViewJsonRenderer');
            break;
            case 'jsonp':
                return function (AbstractFieldValuePart $acceptedFieldValue) use ($serviceLocator) {
                        $renderer = $serviceLocator->get('ViewJsonRenderer');
                        $renderer->setJsonpCallback('callback');
                        return $renderer;
                    };
            break;
            case 'feed':
                return function (AbstractFieldValuePart $acceptedFieldValue) use ($serviceLocator) {
                        $renderer = $serviceLocator->get('ViewFeedRenderer');
                        if (false !== strpos('rss', $acceptedFieldValue->getType())) {
                            $renderer->setFeedType('rss');
                        } else {
                            $renderer->setFeedType('atom');
                        }
                        return $renderer;
                    };
            break;
        }
    }
    
    /**
     * Get the response by alias
     * 
     * @param string $name
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function responseAlias($name, ServiceLocatorInterface $serviceLocator)
    {
        switch (strtolower($name)) {
            case 'json':
            case 'jsonp':
                return function($renderer, $response, $result) {
                            $headers = $response->getHeaders();
                            $headers->addHeaderLine('content-type', 'application/json');
                        };
            break;
            case 'feed':
                return function($renderer, $response, $result) {
                            // Feed Renderer; set content-type header, and export the feed if
                            // necessary
                            $feedType  = $renderer->getFeedType();
                            $headers   = $response->getHeaders();
                            $mediatype = 'application/'
                                       . (('rss' == $feedType) ? 'rss' : 'atom')
                                       . '+xml';
                            $headers->addHeaderLine('content-type', $mediatype);

                            // If the $result is a feed, export it
                            if ($result instanceof Feed) {
                                $result = $result->export($feedType);
                            }
                        };
            break;
        }
    }
    
    /**
     * Check of the given name is a registered alias
     * 
     * @param string $name
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return string
     */
    public function isAlias($name, ServiceLocatorInterface $serviceLocator)
    {
        $name = strtolower($name);
        $config = array(
                'renderer' => $this->rendererAlias($name, $serviceLocator),
                'response' => $this->responseAlias($name, $serviceLocator),
            );
        
        switch (strtolower($name)) {
            case 'json':
                $config['accept'] = array('application/json');
            break;
            case 'jsonp':
                $config['accept'] = array('application/javascript',
                                          'application/jsonp');
            break;
            case 'feed':
                $config['accept'] = array('application/rss+xml',
                                          'application/atom+xml');
            break;
        }
        
        return $config;
    }
}

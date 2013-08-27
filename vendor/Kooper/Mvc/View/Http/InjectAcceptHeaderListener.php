<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
namespace Kooper\Mvc\View\Http;

use Zend\EventManager\EventManagerInterface as Events;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\Http\Headers;

/**
 * Event listener class to inject the accept header when the
 * MvcEvent::EVENT_DISPATCH MVC event is fired.
 * 
 * The accept header is loaded based on the routers configured key ('extension'
 * by default)
 * 
 * A typical config for this would be:
 * <code>
 * <?php
 * array(
 * 'rest_manager' => array(
 *      'default_template' => 'common/api-response.phtml',
 *      'user' => array('disable_authorization' => true),
 *      'accept_strategy' => array(
 *          'criteria' => array(
 *              'json' => array(
 *                      'accept' => array('application/json'),
 *                      'model' => 'Zend\View\Model\JsonModel',
 *                      'extensions' => array('.json', '.jsn', '.jason'),
 *                  ),
 *              'jsonp' => array(
 *                      'accept' => array('application/javascript', 'application/jsonp'),
 *                      'model' => 'Zend\View\Model\JsonModel',
 *                      'extensions' => array('.jsonp', '.jsnp', '.jasonp', '.js', '.javascript'),
 *                  ),
 *          ),
 *      ),
 *  ),
 * );
 * </code>
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
class InjectAcceptHeaderListener
    implements ListenerAggregateInterface
{
    /**
     * Desfault route key
     */
    const DEFAULT_ROUTE_KEY = 'extension';
    
    /**
     * Default accept header
     */
    const DEFAULT_ACCEPT_HEADER = '*/*';
    
    /**
     * Header names
     */
    const HEADER_ACCEPT = 'Accept';
    const HEADER_CONTENT_TYPE = 'Content-Type';
    
    /**
     * Route key to lookout for
     * 
     * @var string
     */
    protected $_routeKey = self::DEFAULT_ROUTE_KEY;
    
    /**
     * Default accept header to use
     * 
     * @var string
     */
    protected $_defaultAcceptHeader = self::DEFAULT_ACCEPT_HEADER;
    
    /**
     * Extensions to header mapper
     * 
     * @var array
     */
    protected $_extensionMap = array();
    
    /**
     * Listeners we've registered
     *
     * @var array
     */
    protected $_listeners = array();

    /**
     * Constructor
     * 
     * @param array $options
     * @return void
     */
    public function __construct(array $options = null)
    {
        if ($options) {
            foreach ($options as $key => $value) {
                $method = 'set' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $key)));
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }
    }
    
    /**
     * Attach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function attach(Events $events)
    {
        $this->_listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'injectAcceptHeader'));
    }

    /**
     * Detach listeners
     *
     * @param  Events $events
     * @return void
     */
    public function detach(Events $events)
    {
        foreach ($this->_listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->_listeners[$index]);
            }
        }
    }

    /**
     * Set the route key to scan for the extension to match
     * 
     * @param string $key
     * @return \Kooper\Mvc\View\Http\InjectAcceptHeaderListener
     */
    public function setRouteKey($key)
    {
        $this->_routeKey = (string)$key;
        return $this;
    }
    
    /**
     * Set the default accept header
     * 
     * @param string $header
     * @return \Kooper\Mvc\View\Http\InjectAcceptHeaderListener
     */
    public function setDefaultAcceptHeader($header)
    {
        $this->_defaultAcceptHeader = (string)$header;
        return $this;
    }
    
    /**
     * Set the extension to accept header mapping where the array key is the
     * accept header, and the value is an array of supported extensions
     * 
     * @param array $config
     * @return \Kooper\Mvc\View\Http\InjectAcceptHeaderListener
     */
    public function setExtensions(array $config)
    {
        foreach ($config as $header => $extensions) {
            $this->addExtensions((array)$extensions, $header);
        }
        
        return $this;
    }
    
    /**
     * Add an array of extensions to an accept header
     * 
     * @param array $extensions
     * @param string $accept
     * @return \Kooper\Mvc\View\Http\InjectAcceptHeaderListener
     */
    public function addExtensions(array $extensions, $accept = null)
    {
        if (null === $accept) {
            $accept = $this->_defaultAcceptHeader;
        }
        
        $extensions = array_change_key_case($extensions);
        
        if (isset($this->_extensionMap[$accept])) {
            $this->_extensionMap[$accept] = array_merge($this->_extensionMap[$accept], $extensions);
        }
        
        $this->_extensionMap[$accept] = $extensions;
        
        return $this;
    }
    
    /**
     * Replace the header key with the value in the header set
     * 
     * @param \Zend\Http\Headers $headers
     * @param string $value
     * @param string $name
     * @return bool
     */
    protected function _replaceHeader(Headers $headers, $value, $name = self::HEADER_ACCEPT)
    {
        if (false !== ($accept = $headers->get($name))) {
            $headers->removeHeader($accept);
        }
        return $headers->addHeaders(array($name => $value));
    }
    
    /**
     * Get the route key and match against the model criteria
     * If a match is found, set the accomodating accept header
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectAcceptHeader(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if (null !== ($extension = $routeMatch->getParam($this->_routeKey))) {
            $extension = strtolower($extension);
            foreach ($this->_extensionMap as $acceptHeader => $extensions) {
                if (in_array($extension, $extensions)) {
                    $request = $e->getRequest();
                    $response = $e->getResponse();
                    
                    $this->_replaceHeader($request->getHeaders(), $acceptHeader, self::HEADER_ACCEPT);
                    $this->_replaceHeader($response->getHeaders(), $acceptHeader, self::HEADER_CONTENT_TYPE);

                    break;
                }
            }
        }
    }
}

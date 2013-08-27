<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
namespace Kooper\Mvc\Controller\Plugin\Strategy;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;

/**
 * User strategy prepares the user object and assigning events to the MVC event
 * manager checking the ACL status of the requests SSO token by using the
 * registered AuthorizationPlugin controller plugin
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
class UserStrategy
    implements ListenerAggregateInterface
{
    /**
     * Flag to disable the authorization
     * 
     * @var bool
     */
    protected $_disableAuthorization = false;
    
    /**
     * Listeners
     * 
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $_listeners = array();
    
    /**
     * Identifier(s) for the shared event emitting component(s) associated to
     * authorization
     * 
     * @var string|array
     */
    protected $_sharedManagerId = 'Zend\Stdlib\DispatchableInterface';
    
    /**
     * Plugin config options
     * 
     * @param array 
     */
    protected $_pluginConfig = array();
    
    /**
     * Constructor
     * 
     * Accepts an array of configuration options:
     * - disable_authorization : proxies to self::disableAuthorization()
     * 
     * @param array $options
     * @return void
     */
    public function __construct(array $options = null)
    {
        if ($options) {
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                    case 'disable_authorization':
                        if (true === $value) {
                            $this->disableAuthorization();
                        }
                    break;
                    default :
                        $this->_pluginConfig[$key] = $value;
                    break;
                }
            }
        }
    }
    
    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->_listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'loadPlugin'), 1);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->_listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->_listeners[$index]);
            }
        }
    }
    
    /**
     * Disable the authorization
     * 
     * @return Kooper\Mvc\Controller\Plugin\Strategy\AuthorizationStrategy
     */
    public function disableAuthorization()
    {
        $this->_disableAuthorization = true;
        return $this;
    }
            
    /**
     * Load the controller UserPlugin, get the user instance and assign the
     * request object to work out the SSO token
     * 
     * If authorization is allowed, attach a new MvcEvent::EVENT_DISPATCH on the
     * $_sharedManagerId instance(s)
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function loadPlugin(MvcEvent $e)
    {
        $application   = $e->getApplication();
        $sm            = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
        $request       = $sm->get('request');

        $plugin = $sm->get('ControllerPluginManager')->get('UserPlugin');
        $user   = $sm->get('User');
        
        $plugin->setUser($user)
               ->setSso($request);
        
        if ($this->_disableAuthorization) {
            $plugin->disableAuthorization();
            return;
        }
        
        if ($this->_pluginConfig) {
            $plugin->setOptions($this->_pluginConfig);
        }
        
        $router        = $sm->get('router');
        $matchedRoute  = $router->match($request);
        
        if (null !== $matchedRoute) {
            $sharedManager->attach($this->_sharedManagerId, MvcEvent::EVENT_DISPATCH, array($this, 'doAuthorization'), 2);
        }
    }
    
    /**
     * Load the controller UserPlugin, get the user instance, assign the
     * request object to work out the SSO token and check if
     * the user token is authorized to access the current route
     * 
     * @param \Zend\Mvc\MvcEvent $e
     * @return void
     */
    public function doAuthorization(MvcEvent $e)
    {
        $application    = $e->getApplication();
        $sm             = $application->getServiceManager();
        $plugin         = $sm->get('ControllerPluginManager')->get('UserPlugin');
        $plugin->doAuthorization($e);
    }
}

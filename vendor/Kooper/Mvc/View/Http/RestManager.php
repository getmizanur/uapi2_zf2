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

use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Kooper\Mvc\Controller\Plugin\Strategy\UserStrategy;
use Kooper\Mvc\View\Http\InjectDefaultTemplateListener;
use Kooper\Mvc\View\Http\InjectAcceptHeaderListener;

/**
 * Prepares and configures the complete rest layer based ont he module
 * configuration
 * 
 * An example configuration is:
 * <code>
 * 'rest_manager' => array(
 *     'default_template' => 'common/api-response.phtml',
 *     'user' => array('disable_authorization' => true),
 *     'accept_strategy' => array(
 *         'criteria' => array(
 *             'json' => array(
 *                 'accept' => array('application/json'),
 *                 'model' => 'Zend\View\Model\JsonModel',
 *             ),
 *             'jsonp' => array(
 *                 'accept' => array('application/javascript', 'application/jsonp'),
 *                 'model' => 'Zend\View\Model\JsonModel',
 *             ),
 *         ),
 *     ),
 * ),
 * </code>
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
class RestManager
    implements ListenerAggregateInterface
{
    /**
     * Listeners
     * 
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $_listeners = array();
    
    /**
     * Application configuration service
     * 
     * @var object 
     */
    protected $_config;
    
    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->_listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'onBootstrap'));
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
     * Prepares the rest layer
     *
     * @param MvcEvent $event
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application   = $event->getApplication();
        $services      = $application->getServiceManager();
        $config        = $services->get('Config');
        $events        = $application->getEventManager();
        $sharedEvents  = $events->getSharedManager();
        $this->_config = isset($config['rest_manager']) && (is_array($config['rest_manager']) || $config['rest_manager'] instanceof ArrayAccess)
                         ? $config['rest_manager']
                         : array();
        
        $events->attach($this->getUserStrategy());
        
        $restExceptionStrategy = $services->get('RestExceptionStrategyFactory');
        if ($restExceptionStrategy instanceof ListenerAggregateInterface) {
            $events->attach($restExceptionStrategy);
        }
        
        if (isset($this->_config['default_template'])) {
            $injectDefaultTemplateListener = new InjectDefaultTemplateListener(array('default_template' => $this->_config['default_template']));
            $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectDefaultTemplateListener, 'injectTemplate'), -89);
        }
        
        $injectAcceptHeaderListener = $this->getInjectAcceptHeaderListener();
        if ($injectAcceptHeaderListener instanceof ListenerAggregateInterface) {
            $events->attach($injectAcceptHeaderListener);
        }
    }
    
    /**
     * Configure and return the InjectAcceptHeaderListener
     * 
     * @return \Kooper\Mvc\View\Http\InjectAcceptHeaderListener
     */
    public function getInjectAcceptHeaderListener()
    {
        if (isset($this->_config['accept_strategy'])
            && isset($this->_config['accept_strategy']['criteria'])
        ) {
            $acceptHeaderConfig = array('extensions' => array());
            foreach ($this->_config['accept_strategy']['criteria'] as $spec) {
                if (isset($spec['accept']) && is_array($spec['accept']) && isset($spec['extensions'])) {
                    foreach ($spec['accept'] as $acceptHeader) {
                        if (isset($acceptHeaderConfig['extensions'][$acceptHeader])) {
                            $acceptHeaderConfig['extensions'][$acceptHeader] = array_merge($acceptHeaderConfig['extensions'][$acceptHeader], (array)$spec['extensions']);
                        } else {
                            $acceptHeaderConfig['extensions'][$acceptHeader] = (array)$spec['extensions'];
                        }
                    }
                }
            }
            
            if (isset($this->_config['default_accept_header'])) {
                $acceptHeaderConfig['default_accept_header'] = $this->_config['default_accept_header'];
            }
            
            if (isset($this->_config['route_key'])) {
                $acceptHeaderConfig['route_key'] = $this->_config['route_key'];
            }
            
            return new InjectAcceptHeaderListener($acceptHeaderConfig);
        }
    }
    
    /**
     * Configure and return the UserStrategy
     * 
     * @return UserStrategy
     */
    public function getUserStrategy()
    {
        $config = $this->_config;
        
        $options = null;
        if (isset($config['user'])) {
            $options = $config['user'];
        }
        
        $userStrategy = new UserStrategy($options);
        return $userStrategy;
    }
}
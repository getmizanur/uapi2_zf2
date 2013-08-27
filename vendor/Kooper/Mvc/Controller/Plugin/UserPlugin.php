<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Plugins
 */
namespace Kooper\Mvc\Controller\Plugin;

use Kooper\User\User;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request;
use Zend\Mvc\ModuleRouteListener;
use Zend\Filter\Word\CamelCaseToDash;

/**
 * User controller plugin
 * 
 * Checks the request object for an SSO token and assigns it to the service
 * managers User object. If allowed sets events to verify if the SSO token has
 * access to the intended module/controller/verb
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage Plugins
 */
class UserPlugin
    extends AbstractPlugin
{
    /**
     * Name of the header to check for an SSO token
     */
    const HEADER_NAME = "Authorization";
    
    /**
     * Name of the query string key to check for an SSO token
     */
    const QUERY_KEY = "sso";
    
    /**
     * User instance
     * 
     * @var Kooper\User\User
     */
    protected $_user;
    
    /**
     * Flag to disable the authorization
     * 
     * @var bool
     */
    protected $_disableAuthorization = false;
    
    /**
     * Route match param key for checking if authorization is required
     * 
     * @var string
     */
    protected $_routeKey = 'disable_authorization';
    
    /**
     * Flag to state if authorization should be required or not if the route
     * parameter is not present
     * 
     * @var string
     */
    protected $_authorizedByDefault = true;
    
    /**
     * FilterInterface/inflector used to normalize names for use as template identifiers
     *
     * @var mixed
     */
    protected $_inflector;
    
    /**
     * Set the plugin options
     * 
     * @param array $options
     * @return \Kooper\Mvc\Controller\Plugin\UserPlugin
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucfirst(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        
        return $this;
    }
    
    /**
     * Set the user instance
     * 
     * @param \Kooper\User\User $user
     * @return \Kooper\Mvc\Controller\Plugin\UserPlugin
     */
    public function setUser(User $user)
    {
        $this->_user = $user;
        return $this;
    }
    
    /**
     * Get the user instance
     * 
     * @return Kooper\User\User
     */
    public function getUser()
    {
        if (!$this->_user) {
            throw new \Exception("No user defined", 401);
        }
        return $this->_user;
    }
    
    /**
     * Disable the authorization
     * 
     * @return \Kooper\Mvc\Controller\Plugin\UserPlugin
     */
    public function disableAuthorization()
    {
        $this->_disableAuthorization = true;
        return $this;
    }
    
    /**
     * Set the route key to scan for the extension to match
     * 
     * @param string $key
     * @return Kooper\Mvc\Controller\Plugin\Strategy\AuthorizationStrategy
     */
    public function setRouteKey($key)
    {
        $this->_routeKey = (string)$key;
        return $this;
    }
    
    /**
     * Set the flag to state if authorization should be required or not if the
     * route parameter is not present
     * 
     * @param bool $flag
     * @return Kooper\Mvc\Controller\Plugin\Strategy\AuthorizationStrategy
     */
    public function setAuthorizeByDefault($flag)
    {
        $this->_authorizedByDefault = (bool)$flag;
        return $this;
    }
    
    /**
     * Map the requst verb to the appropriate authorization permission
     * 
     * @param string $method
     * @return string
     */
    public function mapMethodToPermission($method)
    {
        $method = strtolower($method);
        
        switch ($method) {
            case 'get':
                return 'read';
            break;
        
            case 'post':
                return 'create';
            break;
        
            case 'put':
                return 'update';
            break;
        
            case 'delete':
            default:
                return $method;
            break;
        }
    }
    
    /**
     * Get the SSO token from the given request object and inject it into
     * the assigned user object
     * 
     * Fetches the SSO token from either the self::HEADER_NAME in the request
     * headers, or the query strings self::QUERY_KEY value
     * 
     * If both are set, the self::QUERY_KEY takes priority
     * 
     * @param Request $request
     * @return \Kooper\Mvc\Controller\Plugin\UserPlugin
     */
    public function setSso(Request $request)
    {
        if (!($sso = rawurlencode($request->getQuery(self::QUERY_KEY)))
              && null !== ($headers = $request->getHeaders())
              && $headers->has(self::HEADER_NAME)
        ) {
            $sso = $request->getHeaders()->get(self::HEADER_NAME)->value;
        }
        
        if ($sso) {
            $user = $this->getUser();
            $user->setSso($sso);
        }
        
        return $this;
    }
    
    /**
     * Check if authorization is required for the current request by checking
     * the config and the route match for the configured key
     * 
     * @param  MvcEvent $e
     * @return bool
     */
    public function requiresAuthorization(MvcEvent $e)
    {
        if (false === $this->_disableAuthorization) {
            $routeMatch = $e->getRouteMatch();
            if ($routeMatch) {
                return (bool) !$routeMatch->getParam($this->_routeKey, !$this->_authorizedByDefault);
            }
            
            return $this->_authorizedByDefault;
        }
        
        return $this->_disableAuthorization;
    }
    
    /**
     * The main interaction with the plugin
     * 
     * Check the Mvc request object, fetch the SSO token from the request and
     * check if the user is authorized against the following criteria:
     * - Resource   - The controller class name
     * - Module     - The Zend module name
     * - Permission - Request verb - {@see self::mapMethodToPermission()}
     * 
     * @param \Zend\Mvc\MvcEvent $e
     * @return boolean TRUE if authorized, or throws an exception
     * @throws \Exception
     */
    public function doAuthorization(MvcEvent $e)
    {
        if (false === $this->requiresAuthorization($e)) {
            return true;
        }
        
        $application     = $e->getApplication();
        $sm              = $application->getServiceManager();
        $request         = $sm->get('request');
        $routeMatch      = $e->getRouteMatch();
        $controller      = $e->getTarget();
        
        $permission      = $this->mapMethodToPermission($request->getMethod());
        
        if (is_object($controller)) {
            $controller = get_class($controller);
        }
        if (!$controller) {
            $controller = $routeMatch->getParam('controller', '');
        }
        
        $module = $this->deriveModuleNamespace($controller);
        if (null != ($namespace = $routeMatch->getParam(ModuleRouteListener::MODULE_NAMESPACE))) {
            $controllerSubNs = $this->deriveControllerSubNamespace($namespace);
            if (!empty($controllerSubNs)) {
                if (!empty($module)) {
                    $module .= '/' . $controllerSubNs;
                } else {
                    $module = $controllerSubNs;
                }
            }
        }
        
        $derivedController = $this->deriveControllerClass($controller);
        $resource = $this->inflectName($derivedController);
        $action = $routeMatch->getParam('action');
        
        if (null !== $action) {
            $resource .= '/' . $this->inflectName($action);
        }

        if (!$this->getUser()->isAllowed($resource, $permission, $module)){
            throw new \Exception(sprintf('Permission denied for route %1$s / %2$s : %3$s', $module, $resource, $permission), 403);
        }
        
        return true;
    }
    
    /**
     * Inflect a name to a normalized value
     *
     * @param  string $name
     * @return string
     */
    protected function inflectName($name)
    {
        if (!$this->_inflector) {
            $this->_inflector = new CamelCaseToDash();
        }
        $name = $this->_inflector->filter($name);
        return strtolower($name);
    }
    
    /**
     * Determine the top-level namespace of the controller
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveModuleNamespace($controller)
    {
        if (!strstr($controller, '\\')) {
            return '';
        }
        $module = strtolower(substr($controller, 0, strpos($controller, '\\')));
        return $module;
    }
    
    /**
     * Determine the sub-level namespace of the controller
     * 
     * @param $namespace
     * @return string
     */
    protected function deriveControllerSubNamespace($namespace)
    {
        if (!strstr($namespace, '\\')) {
            return '';
        }
        $nsArray = explode('\\', $namespace);

        // Remove the first two elements representing the module and controller directory.
        $subNsArray = array_slice($nsArray, 2);
        if (empty($subNsArray)) {
            return '';
        }
        return implode('/', $subNsArray);
    }
    
    /**
     * Determine the name of the controller
     *
     * Strip the namespace, and the suffix "Controller" if present.
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveControllerClass($controller)
    {
        if (strstr($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }

        if ((10 < strlen($controller))
            && ('Controller' == substr($controller, -10))
        ) {
            $controller = substr($controller, 0, -10);
        }

        return $controller;
    }
}
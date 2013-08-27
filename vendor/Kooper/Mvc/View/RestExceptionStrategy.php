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
namespace Kooper\Mvc\View;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

/**
 * REST exception strategy service
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
class RestExceptionStrategy
    implements ListenerAggregateInterface
{
    /**
     * Default accept header
     */
    const DEFAULT_ACCEPT_HEADER = '*/*';
    
    /**
     *
     * @var string Default ViewModel
     */
    protected $defaultViewModelName = 'Zend\View\Model\ViewModel';
    
    /**
     * Default accept header to use
     * 
     * @var string
     */
    protected $defaultAcceptHeader = self::DEFAULT_ACCEPT_HEADER;
    
    /**
     * Display exceptions?
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * Name of exception template
     * @var string
     */
    protected $exceptionTemplate = 'error';

    /**
     * Listeners
     * 
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Accept header criteria
     * 
     * @var array
     */
    protected $_acceptCriteria = array();
    
    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareExceptionViewModel'), 1);
    }

    /**
     * Detach aggregate listeners from the specified event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Set the default accept header
     * 
     * @param string $header
     * @return \Kooper\Mvc\View\RestExceptionStrategy
     */
    public function setDefaultAcceptHeader($header)
    {
        $this->defaultAcceptHeader = (string)$header;
        return $this;
    }
    
    /**
     * Set the accept header criteria
     * 
     * @param array $criteria
     * @return \Kooper\Mvc\View\RestExceptionStrategy
     */
    public function setAcceptHeaderCriteria(array $criteria)
    {
        foreach ($criteria as $modelName => $acceptMatch) {
            $this->addAcceptHeaderCriteria($modelName, $acceptMatch);
        }
        
        return $this;
    }
    
    /**
     * Add an accept header pattern to match against a given model
     * 
     * @param string $modelName
     * @param array $acceptMatch
     * @return \Kooper\Mvc\View\RestExceptionStrategy
     */
    public function addAcceptHeaderCriteria($modelName, $acceptMatch)
    {
        if (!is_array($acceptMatch)) {
            $acceptMatch = array($acceptMatch);
        }
        
        if (isset($this->_acceptCriteria[$modelName])) {
            $this->_acceptCriteria[$modelName] = array_merge($this->_acceptCriteria[$modelName], $acceptMatch);
        } else {
            $this->_acceptCriteria[$modelName] = $acceptMatch;
        }
        
        return $this;
    }
    
    /**
     * Flag: display exceptions in error pages?
     *
     * @param  bool $displayExceptions
     * @return ExceptionStrategy
     */
    public function setDisplayExceptions($displayExceptions)
    {
        $this->displayExceptions = (bool) $displayExceptions;
        return $this;
    }

    /**
     * Should we display exceptions in error pages?
     *
     * @return bool
     */
    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    /**
     * Set the exception template
     *
     * @param  string $exceptionTemplate
     * @return ExceptionStrategy
     */
    public function setExceptionTemplate($exceptionTemplate)
    {
        $this->exceptionTemplate = (string) $exceptionTemplate;
        return $this;
    }

    /**
     * Retrieve the exception template
     *
     * @return string
     */
    public function getExceptionTemplate()
    {
        return $this->exceptionTemplate;
    }

    /**
     * Create an exception view model, and set the HTTP status code
     *
     * @todo   dispatch.error does not halt dispatch unless a response is
     *         returned. As such, we likely need to trigger rendering as a low
     *         priority dispatch.error event (or goto a render event) to ensure
     *         rendering occurs, and that munging of view models occurs when
     *         expected.
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }
        
        $exception = $e->getParam('exception');
        if (empty($exception)) {
            return;
        }

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                // Specifically not handling these
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $code = $exception->getCode()?: 500;
                $model = $this->getViewModel($e);
                $modelName = get_class($model);
                
                $variables = $this->_getExceptionVariables($exception, $modelName);
                
                if (null !== ($previous = $exception->getPrevious())) {
                    $variables['previous'] = $this->_getExceptionVariables($previous, $modelName);
                }
                
                $model->setVariables($variables);

                $model->setTemplate($this->getExceptionTemplate());
                $e->setResult($model);

                $response = $e->getResponse();
                if (!$response) {
                    $response = new HttpResponse();
                    $e->setResponse($response);
                }
                
                $httpResponseCodeConst = get_class($response) . '::STATUS_CODE_' . $code;
                if (!defined($httpResponseCodeConst)) {
                    $code = 500;
                }
                $response->setStatusCode($code);
                $e->getError(false);
                
                break;
        }
    }
    
    /**
     * Prepare the variables from the given exception
     * 
     * @param \Exception $exception
     * @param string $modelName
     * @return array
     */
    protected function _getExceptionVariables(\Exception $exception, $modelName)
    {
        $variables = array(
            'message'            => $exception->getMessage(),
            'exception'          => $exception,
            'display_exceptions' => $this->displayExceptions(),
        );

        if (false === strpos($modelName, 'ViewModel')
            && $this->displayExceptions()
        ) {
            $variables['exception'] = array(
                    'file' => $exception->getFile(),
                    'message' => $exception->getMessage(),
                    'trace_as_string' => $exception->getTraceAsString(),
                );
        }
        
        return $variables;
    }
    
    /**
     * Get the relevant view model depending on the results of the accept header
     * 
     * @param \Zend\Mvc\MvcEvent $e
     * @return \Kooper\Mvc\View\model
     */
    public function getViewModel(MvcEvent $e)
    {
        try {
            $application      = $e->getApplication();
            $controllerLoader = $application->getServiceManager()->get('ControllerLoader');
            $controllerName   = $e->getController();
            $controller = $controllerLoader->get($controllerName);
            $acceptableViewModelSelector = $controller->plugin('acceptableViewModelSelector');
            $matchAgainst = $this->_acceptCriteria;
            $matchAgainst[$this->defaultViewModelName] = $this->defaultAcceptHeader;
            $model = $acceptableViewModelSelector->getViewModel($matchAgainst);
        } catch (ServiceNotFoundException $exception) {
            $model = $this->defaultViewModelName;
        }
        
        if (!$model) {
            $model = $this->defaultViewModelName;
        }
        
        return new $model();
    }
}

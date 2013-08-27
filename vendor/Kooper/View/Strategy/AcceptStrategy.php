<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link       https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright  Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category   Kooper
 * @package    Kooper_View
 * @subpackage Strategy
 */
namespace Kooper\View\Strategy;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\View\ViewEvent;
use Zend\View\Renderer\RendererInterface;
use Zend\Http\Header\Accept\FieldValuePart\AbstractFieldValuePart;

/**
 * View strategy to fetch the correct view model based on the requests accept
 * headers
 * 
 * @category   Kooper
 * @package    Kooper_View
 * @subpackage Strategy
 */
class AcceptStrategy
    implements ListenerAggregateInterface
{
    /**
     * Criteria name
     */
    const CRITERIA_NAME = "_criteriaName";
    
    /**
     * Array of listeners
     * 
     * @var array
     */
    protected $_listeners = array();

    /**
     * Default renderer
     * 
     * @var RendererInterface
     */
    protected $_defaultRenderer;
    
    /**
     * Accept criteria configuration
     * 
     * @var array
     */
    protected $_acceptCriteria = array();

    /**
     * Set the default renderer
     * 
     * @param RendererInterface $renderer
     * @return \Kooper\View\Strategy\AcceptStrategy
     */
    public function setDefaultRenderer(RendererInterface $renderer)
    {
        $this->_defaultRenderer = $renderer;
        return $this;
    }
    
    /**
     * Set the accept criteria
     * 
     * @param array $criteria
     * @return \Kooper\View\Strategy\AcceptStrategy
     */
    public function setAcceptCriteria(array $criteria)
    {
        foreach ($criteria as $name => $options) {
            $this->addAcceptCriteria($name, $options);
        }
        
        return $this;
    }
    
    /**
     * Expects criteria to be an array with the following key value pairs:
     * - accept
     *   Index array of accept
     * 
     * @param type $name
     * @param array $criteria
     * @return \Kooper\View\Strategy\AcceptStrategy
     * @throws \Exception
     */
    public function addAcceptCriteria($name, array $criteria)
    {
        if (isset($criteria['accept'])) {
            if (!is_array($criteria['accept'])) {
                $criteria['accept'] = array(strval($criteria['accept']));
            }
        } else {
            $criteria['accept'] = array();
        }
        
        if (!(isset($criteria['renderer'])
              && ($criteria['renderer'] instanceof RendererInterface
                  || $criteria['renderer'] instanceof \Closure))
        ) {
            throw new \Exception("You must set a renderer. Either an instance of RendererInterface or Closure");
        }
        
        if (!(isset($criteria['response']) && $criteria['response'] instanceof \Closure)) {
            $criteria['response'] = null;
        }
        
        $this->_acceptCriteria[strval($name)] = $criteria;
        return $this;
    }
    
    /**
     * Attach the aggregate to the specified event manager
     *
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->_listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->_listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
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
     * Inject the criteria name into the accept header string
     *
     * @param string $acceptString
     * @param string $name
     * @return string
     */
    protected function injectCriteriaName($acceptString, $name = null)
    {
        return $acceptString . '; ' . self::CRITERIA_NAME . '="' . $name . '", ';
    }
    
    /**
     * Extract the criteria name from a match
     * @param AbstractFieldValuePart $res
     * @return string
     */
    protected function extractCriteriaName(AbstractFieldValuePart $res)
    {
        return $res->getMatchedAgainst()->params[self::CRITERIA_NAME];
    }
    
    /**
     * Get the appropriate renderer
     * 
     * @param ViewEvent $e
     * @return \Kooper\View\Strategy\Closure
     */
    public function selectRenderer($e)
    {
        $request = $e->getRequest();
        $headers = $request->getHeaders();

        if (!$headers->has('accept')) {
            return $this->_defaultRenderer;
        }

        $matchAgainstString = '';
        foreach ($this->_acceptCriteria as $name => $criteria) {
            foreach ((array) $criteria['accept'] as $accept) {
                $matchAgainstString .= $this->injectCriteriaName($accept, $name);
            }
        }
        $matchAgainstString .= $this->injectCriteriaName('*/*');
        
        /** @var $accept \Zend\Http\Header\Accept */
        $accept = $headers->get('Accept');
        $criteriaName = null;
        
        $res = $accept->match($matchAgainstString);
        
        if ($res) {
            $criteriaName = $this->extractCriteriaName($res);
        }
        
        if (!($criteriaName && array_key_exists($criteriaName, $this->_acceptCriteria))) {
            return $this->_defaultRenderer;
        }
        
        $acceptedCriteria = $this->_acceptCriteria[$criteriaName];
        if ($acceptedCriteria['renderer'] instanceof \Closure) {
            $closure = $acceptedCriteria['renderer'];
            return $closure($res);
        }
        
        return $acceptedCriteria['renderer'];
    }

    /**
     * Inject the response
     * 
     * @param ViewEvent $e
     * @return void
     */
    public function injectResponse($e)
    {
        $renderer = $e->getRenderer();
        $response = $e->getResponse();
        $result   = $e->getResult();
        $found    = false;
        
        foreach ($this->_acceptCriteria as $criteria) {
            if ($renderer === $criteria['renderer']) {
                if (isset($criteria['response']) && $criteria['response'] instanceof \Closure) {
                    $closure = $criteria['response'];
                    $closure($renderer, $response, $result);
                }
                $found = true;
                break;
            }
        }
        
        if ($found === false && $renderer !== $this->_defaultRenderer) {
            // Not a renderer we support, therefor not our strategy. Return
            return;
        }
        
        // Inject the content
        $response->setContent($result);
    }
}
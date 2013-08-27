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
use Zend\View\Model\ModelInterface as ViewModel;

/**
 * Event listener class to inject a default template when the
 * MvcEvent::EVENT_DISPATCH MVC event is fired.
 * 
 * The default template is passed into the constructor with the array key
 * 'default_template'
 * 
 * @category   Kooper
 * @package    Kooper_Mvc
 * @subpackage View
 */
class InjectDefaultTemplateListener
    implements ListenerAggregateInterface
{
    /**
     * Default template
     * 
     * @var string
     */
    protected $_defaultTemplate;
    
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
        if ($options && isset($options['default_template'])){
            $this->setDefaultTemplate($options['default_template']);
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
        $this->_listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'), -89);
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
     * Set the default template
     * 
     * @param string $template
     * @return \Kooper\Mvc\View\Http\InjectTemplateListener
     */
    public function setDefaultTemplate($template)
    {
        $this->_defaultTemplate = (string)$template;
        return $this;
    }
    
    /**
     * Inject the registered default template into the view model, if none
     * present
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (!$model instanceof ViewModel) {
            return;
        }

        $template = $model->getTemplate();
        if (!empty($template)) {
            return;
        }

        $template = $this->_defaultTemplate;
        if (empty($template)) {
            return;
        }
        
        $model->setTemplate($template);
    }
}

<?php
/**
 * Kooper library (https://bitbucket.org/ottilus/library-kooper)
 *
 * @link      https://bitbucket.org/ottilus/library-kooper for the canonical source repository
 * @copyright Copyright (c) 2013 OTTilus Ltd. (http://www.ottilus.com)
 * @category  Kooper
 * @package   Kooper_Model
 */
namespace Kooper\Model;

use JsonSerializable;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Abstract entity model which all entities should extend.
 * 
 * Typically used as an ArrayObject Prototype instance with the resultset objects
 * when querying the database
 * 
 * Also, encourages the use of Zends InputFilter to validate properties for use
 * throughout the application
 * 
 * @category  Kooper
 * @package   Kooper_Model
 */
abstract class AbstractEntity
    implements InputFilterAwareInterface, JsonSerializable
{
    /**
     * InputFilter used to validate the given data
     * 
     * @var InputFilter 
     */
    protected $_inputFilter;
    
    /**
     * Construct
     * 
     * @param array|\ArrayAccess $data
     * @return void
     */
    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->exchangeArray($data);
        }
    }
    
    /**
     * Magic method to set a public access property
     * 
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->set($property, $value);
    }
    
    /**
     * Magic method to get a public access property
     * 
     * @param string $property
     */
    public function __get($property)
    {
        return $this->get($property);
    }
    
    /**
     * Unset a registered entity property
     * 
     * @param string $property
     */
    public function __unset($property)
    {
        if (property_exists($this, "_$property")
            && $this->getInputFilter()->has($property)
        ) {
            unset($this->{"_$property"});
        }
    }
    
    /**
     * Set a value to one of the public access properties
     * 
     * @param string $property
     * @param mixed $value
     * @return \Kooper\Model\EntityAbstract
     * @throws \InvalidArgumentException
     */
    public function set($property, $value)
    {
        if (!(is_string($property) && $property)) {
            throw new \InvalidArgumentException("Property name must be a string");
        }
        
        if ($this->getInputFilter()->has($property)) {
            $methodSuffix = str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
            $setMethod = "set" . $methodSuffix;
            $addMethod = "add" . rtrim($methodSuffix, 's');
            
            if (method_exists($this, $addMethod)
                && !(is_array($value) && is_numeric(key($value)))
            ) {
                return $this->$addMethod($value);
            } else if (method_exists($this, $setMethod)) {
                return $this->$setMethod($value);
            } else if (property_exists($this, "_$property")) {
                $input = $this->getInputFilter()->get($property);
                if ($input->setValue($value)->isValid()) {
                    $this->{"_$property"} = $input->getValue();
                } else {
                    throw new \InvalidArgumentException("Invalid $property value '$value'");
                }
                
                return $this;
            }
        }
        
        throw new \InvalidArgumentException(sprintf("'$property' does not exist in class %s", get_class($this)));
    }
    
    /**
     * Get the value of the given public access property wrapped with the input
     * checking proxying to self::_get()
     * 
     * @param string $property
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($property)
    {
        if (!(is_string($property) && $property)) {
            throw new \InvalidArgumentException("Property name must be a string");
        }
        
        if ($this->getInputFilter()->has($property)) {
            return $this->_get($property);
        }
        
        throw new \InvalidArgumentException("'$property' does not exist");
    }
    
    /**
     * Get the value of the given public access property
     * 
     * @param string $property
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function _get($property)
    {
        $method = "get" . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        if (method_exists($this, $method)) {
            return $this->$method();
        } else if (property_exists($this, "_$property")) {
            return $this->{"_$property"};
        }
        
        throw new \InvalidArgumentException("'$property' does not exist");
    }
    
    /**
     * Hydrate the model with the given data array.
     * 
     * Assumes the array key is the proeprty being set and the value is the new
     * value of the property
     * 
     * @uses self::set()
     * @param \ArrayAccess|array $data
     * @param bool $suppressErrors
     * @return Kooper\Model\EntityAbstract
     */
    public function exchangeArray($data, $suppressErrors = false)
    {
        if (is_array($data) || $data instanceof \ArrayAccess) {
            foreach ($data as $key => $value) {
                if (false !== ($pos = strpos($key, '__'))) {
                    $objectName = substr($key, 0, $pos);
                    $objectProperty = substr($key, $pos+2);
                    $data[$objectName][$objectProperty] = $value;
                    unset($data[$key]);
                }
            }
            
            foreach ($data as $key => $value) {
                try {
                    $this->set($key, $value);
                } catch (\Exception $e) {
                    if (false === $suppressErrors) {
                        throw $e;
                    }
                }
            }
        }
        
        return $this;
    }
    
    /**
     * To satisfy the JsonSerializable interface allowing for the object to be
     * json encoded, proxy to getArrayCopy()
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }
    
    /**
     * To satisy various zend components such as the resultset, proxy to
     * getArrayCopy()
     * 
     * @param array|null $properties
     * @return array
     */
    public function toArray($properties = null)
    {
        return $this->getArrayCopy($properties);
    }
    
    /**
     * Get an array of the public access properties and their values
     * 
     * @param array|null $properties
     * @return array
     */
    public function getArrayCopy(array $properties = null)
    {
        $return = array();
        $properties = $this->getArrayProperties($properties);
        
        foreach ($properties as $property) {
            $name = ltrim($property, '_');
            $value = $this->_get($name);
            if (null !== $value) {
                $return[$name] = $this->_valueToArray($value);
            }
        }
        return $return;
    }
    
    /**
     * Get a list of supported array properties
     * 
     * @param array $properties
     */
    public function getArrayProperties(array $properties = null)
    {
        $inputFilter = $this->getInputFilter();
        
        if (null === $properties) {
            $properties = array_keys(get_object_vars($this));
        }
        
        $filtered = array_filter($properties, function($name) use ($inputFilter) {
                return $inputFilter->has(ltrim($name, "_"));
            });
            
        return $filtered;
    }
    
    /**
     * Cast the given value into either an array or scalar value
     * 
     * @param mixed $value
     * @return mixed
     */
    protected function _valueToArray($value)
    {
        if (is_array($value) && $value) {
            $return = array();
            foreach ($value as $key => $subvalue) {
                $return[$key] = $this->_valueToArray($subvalue);
            }
            return $return;
        } else if (method_exists($value, 'toArray')) {
            return $value->toArray();
        } else if ($value instanceof ArrayObject) {
            return $value->getArrayCopy();
        } else if (isset($value)) {
            return $value;
        }
    }
    
    /**
     * Set input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return \Kooper\Model\EntityAbstract
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->_inputFilter = $inputFilter;
        return $inputFilter;
    }

    /**
     * Retrieve input filter
     *
     * @return InputFilterInterface
     */
    abstract public function getInputFilter();
}
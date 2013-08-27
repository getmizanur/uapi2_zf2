<?php

namespace Synapse\Model\Entity;

use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;

use Mm\Common\Entity;

use Synapse\Model\Filter\DeviceFilter;

class Device implements Entity
{
    protected $filter;

    public $device_id;
    public $device_name;
    public $device_vutv_device_id;
    public $device_cust_id;
    public $device_active;
    public $device_activation;

    public function exchangeArray($data)
    {
        $this->device_id = (
            isset($data['device_id']) ? 
                $data['device_id'] : null
        );
        $this->device_name = (
            isset($data['device_name']) ? 
                $data['device_name'] : null
        );
        $this->device_vutv_device_id = (
            isset($data['device_vutv_device_id']) ? 
                $data['device_vutv_device_id'] : null
        );
        $this->device_cust_id = (
            isset($data['device_cust_id']) ? 
                $data['device_cust_id'] : null
        );
        $this->device_active = (
            isset($data['device_active']) ? 
                $data['device_active'] : null
        );
        $this->device_activation = (
            isset($data['device_activation']) ? 
                $data['device_activation'] : null
        );
    }

    public function getArrayCopy()
    {
        return array(
            'device_id' => $this->device_id,
            'device_name' => $this->device_name,
            'device_vutv_device_id' => $this->device_vutv_device_id,
            'device_cust_id' => $this->device_cust_id,
            'device_active' => $this->device_active,
            'device_activation' => $this->device_activation,     
        );
    }

    public function setInputFilter(InputFilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    public function getInputFilter()
    {
        if(null === $this->filter) {
            $this->setInputFilter(new DeviceFilter());
        }

        return $this->filter;
    }

    public function isValid()
    {
        $filter = $this->getInputFilter();
        $filter->setData($this->getArrayCopy());
        $valid = $filter->isValid();

        if($valid) {
            $this->fromArray($filter->getValues());
        }

        return $valid;
    }

    public function fromArray($data) 
    {
        foreach($data as $key => $value) {
            switch($key) {
                case 'device_id':
                case 'device_name':
                case 'device_vutv_device_id':
                case 'device_cust_id':
                case 'device_active':
                case 'device_activation':
                    $this->$key = $value;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }
}

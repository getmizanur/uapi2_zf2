<?php

namespace Synapse\Model\Filter;

use Zend\InputFilter\InputFilter;

class DeviceFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'device_id',
            'required' => false,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array('name' => 'int')
            )
        ));
        $this->add(array(
            'name' => 'device_name',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
            )
        ));
        $this->add(array(
            'name' => 'device_vutv_device_id',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
            )
        ));
        $this->add(array(
            'name' => 'device_cust_id',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array('name' => 'int')
            )
        ));
        $this->add(array(
            'name' => 'device_active',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array(
                    'name' => 'Regex',
                    'options' => array(
                        'pattern' => '/1|0|true|false/'
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'device_active',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
            )
        ));
    }
}

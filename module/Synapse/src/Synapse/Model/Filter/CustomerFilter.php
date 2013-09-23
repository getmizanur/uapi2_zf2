<?php

namespace Synapse\Model\Filter;

use Zend\InputFilter\InputFilter;

class CustomerFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'account_code',
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
            'name' => 'company_id',
            'required' => false,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'int')
            )
        ));
        $this->add(array(
            'name' => 'account_state',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array(
                    'name' => 'in_array',
                    'options' => array(
                        
                    )
                ),
            )
        ));
        $this->add(array(
            'name' => 'customer_title',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array('name' => 'alpha')
            )
        ));
        $this->add(array(
            'name' => 'customer_first_name',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array('name' => 'alpha')
            )
        ));
        $this->add(array(
            'name' => 'customer_last_name',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
            'validators' => array (
                array('name' => 'not_empty'),
                array('name' => 'alpha')
            )
        ));
        $this->add(array(
            'name' => 'customer_dob',
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
            'name' => 'customer_username',
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
            'name' => 'customer_email',
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
            'name' => 'customer_password',
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
            'name' => 'customer_address_1',
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
            'name' => 'customer_address_2',
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

    }
}

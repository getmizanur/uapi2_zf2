<?php
namespace Synapse\Model\Filter;

use Zend\InputFilter\InputFilter;

class AuditFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'audit_id',
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
            'name' => 'audit_payment_id',
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
            'name' => 'audit_message',
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
            'name' => 'audit_message',
            'required' => true,
            'filters' => array (
                array('name' => 'html_entities'),
                array('name' => 'strip_tags'),
                array('name' => 'string_trim')
            ),
        ));
    }
}

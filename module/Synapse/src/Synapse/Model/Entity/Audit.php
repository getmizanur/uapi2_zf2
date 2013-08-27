<?php

namespace Synapse\Model\Entity;

use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;

use Mm\Common\Entity;

use Synapse\Model\Filter\AuditFilter;

class Audit implements Entity
{
    protected $filter;

    public $audit_id;
    public $audit_payment_id;
    public $audit_created_on;
    public $audit_message;
    public $audit_message_comment;
    
    public function exchangeArray($data)
    {
        $this->audit_id = (
            isset($data['audit_id') ?
                $data['audit_id'] : null
        );

        $this->audit_payment_id = (
            isset($data['audit_payment_id') ?
                $data['audit_payment_id'] : null
        );

        $this->audit_created_on = (
            isset($data['audit_created_on') ?
                $data['audit_created_on'] : date('Y-m-d H:i:s')
        );

        $this->audit_message = (
            isset($data['audit_message') ?
                $data['audit_message'] : null
        );

        $this->audit_message_comment = (
            isset($data['audit_message_comment') ?
                $data['audit_message_comment'] : null
        );
    }

    public function getArrayCopy()
    {
        return array(
            'audit_id' => $this->audit_id,
            'audit_payment_id' => $this->audit_payment_id,
            'audit_created_on' => $this->audit_created_on,
            'audit_message' => $this->audit_message,
            'audit_message_comment' => $this->audit_message_comment
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
            $this->setInputFilter(new AuditFilter());
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
                case 'audit_id':
                case 'audit_company_id':
                case 'audit_created_on':
                case 'audit_message':
                case 'audit_message_comment':
                    $this->$key = $value;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }
}

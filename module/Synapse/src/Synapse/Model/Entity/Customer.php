<?php

namespace Synapse\Model\Entity;

use Zend\InputFilter\Factory;
use Zend\InputFilter\InputFilterInterface;

use Mm\Common\Entity;

use Synapse\Model\Filter\CustomerFilter;

class Customer implements Entity
{
    protected $filter;

    public $customer_id;
    public $customer_company_id;
    public $customer_title;
    public $customer_first_name;
    public $customer_last_name;
    public $customer_dob;
    public $customer_username;
    public $customer_email;
    public $customer_password;
    public $customer_address_1;
    public $customer_address_2;
    public $customer_address_3;
    public $customer_city;
    public $customer_postcode;
    public $customer_country;
    public $customer_mobile;
    public $customer_pin;
    public $customer_parental_pin;
    public $customer_created;
    public $customer_affiliate_id;
    public $customer_modby;
    public $customer_moddate;

    public function exchangeArray($data)
    {
        $this->customer_id = (
            isset($data['customer_id']) ?
                $data['customer_id'] : null
        );
        $this->customer_company_id = (
            isset($data['customer_company_id']) ?
                $data['customer_company_id'] : null
        );
        $this->customer_title = (
            isset($data['customer_title']) ?
                $data['customer_title'] : null
        );
        $this->customer_first_name = (
            isset($data['customer_first_name']) ?
                $data['customer_first_name'] : null
        );
        $this->customer_last_name = (
            isset($data['customer_last_name']) ?
                $data['customer_last_name'] : null
        );
        $this->customer_dob = (
            isset($data['customer_dob']) ?
                $data['customer_dob'] : null
        );
        $this->customer_username = (
            isset($data['customer_username']) ?
                $data['customer_username'] : null
        );
        $this->customer_email = (
            isset($data['customer_email']) ?
                $data['customer_email'] : null
        );
        $this->customer_password = (
            isset($data['customer_password']) ?
                $data['customer_email'] : null
        );
        $this->customer_address_1 = (
            isset($data['customer_address_1']) ?
                $data['customer_address_1'] : null
        );
        $this->customer_address_2 = (
            isset($data['customer_address_2']) ?
                $data['customer_address_2'] : null
        );
        $this->customer_address_3 = (
            isset($data['customer_address_3']) ?
                $data['customer_address_3'] : null
        );
        $this->customer_city = (
            isset($data['customer_city']) ?
                $data['customer_city'] : null
        );
        $this->customer_postcode = (
            isset($data['customer_postcode']) ?
                $data['customer_postcode'] : null
        );
        $this->customer_country = (
            isset($data['customer_country']) ?
                $data['customer_country'] : null
        );
        $this->customer_mobile = (
            isset($data['customer_mobile']) ?
                $data['customer_mobile'] : null
        );
        $this->customer_pin = (
            isset($data['customer_pin']) ?
                $data['customer_pin'] : null
        );
        $this->customer_parental_pin = (
            isset($data['customer_parental_pin']) ?
                $data['customer_parental_pin'] : null
        );
        $this->customer_activation_code = (
            isset($data['customer_activation_code']) ?
                $data['customer_activation_code'] : null
        );
        $this->customer_created_on = (
            isset($data['customer_created']) ?
                $data['customer_created'] : date('Y-m-d H:i:s')
        );
        $this->customer_affiliate_id = (
            isset($data['customer_affiliate_id']) ?
                $data['customer_affiliate_id'] : null
        );
        $this->customer_modby = (
            isset($data['customer_modby']) ?
                $data['customer_modby'] : null
        );
        $this->customer_modby = (
            isset($data['customer_moddate']) ?
                $data['customer_moddate'] : date('Y-m-d H:i:s')
        );
    }

    public function getArrayCopy()
    {
        return array(
            'customer_id' => $this->customer_id,
            'customer_company_id' => $this->customer_company_id,
            'customer_title' => $this->customer_title,
            'customer_first_name' => $this->customer_first_name,
            'customer_last_name' => $this->customer_last_name,
            'customer_dob' => $this->customer_dob,
            'customer_username' => $this->customer_username,
            'customer_email' => $this->customer_email,
            'customer_password' => $this->customer_password,
            'customer_address_1' => $this->customer_address_1,
            'customer_address_2' => $this->customer_address_2,
            'customer_address_3' => $this->customer_address_3,
            'customer_city' => $this->customer_city,
            'customer_postcode' => $this->customer_postcode,
            'customer_country' => $this->customer_country,
            'customer_mobile' => $this->customer_mobile,
            'customer_pin' => $this->customer_pin,
            'customer_parental_pin' => $this->customer_parental_pin,
            'customer_activation_code' => $this->customer_activation_code,
            'customer_created' => $this->customer_created,
            'customer_affiliate_id' => $this->customer_affiliate_id,
            'customer_modby' => $this->customer_modby,
            'customer_moddate' => $this->customer_moddate,
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
            $this->setInputFilter(new CustomerFilter());
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
                case 'customer_id':
                case 'customer_company_id':
                case 'customer_title':
                case 'customer_first_name':
                case 'customer_last_name':
                case 'customer_dob':
                case 'customer_username':
                case 'customer_email':
                case 'customer_password':
                case 'customer_address_1':
                case 'customer_address_2':
                case 'customer_address_3':
                case 'customer_city':
                case 'customer_postcode':
                case 'customer_country':
                case 'customer_mobile':
                case 'customer_pin':
                case 'customer_parental_pin':
                case 'customer_activation_code':
                case 'customer_created':
                case 'customer_affiliate_id':
                case 'customer_modby':
                case 'customer_moddate':
                    $this->$key = $value;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }
}

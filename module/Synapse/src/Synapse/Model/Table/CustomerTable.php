<?php

namespace Synapse\Model\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\InputFilter\Factory;
use Zend\Db\Sql\Select;

use Synapse\Model\Entity\Customer;

class CustomerTable extends AbstractTable 
{
    protected $table;
    protected $tableName;

    public function __construct($table, Adapter $dbAdapter) 
    {
        $this->table = $this->tableName = $table;
        $this->adapter = $dbAdapter;

        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Customer());

        $this->initialize();
    }

    public function getCustomerById($id) 
    {
       $row = $this->fetchRow(array(
            'customer_id' => $id
        ));
    
        if(!$row) {
            return new \ArrayObject();
        }

        return $row;  
    }    

    public function getCustomerByActivationCode($code)
    {
        $row = $this->fetchRow(array(
            'customer_activation_code' => $code
        ));
    
        if(!$row) {
            return null;
        }

        return $row;    
    }  

    public function authenticate($userId, $pin)
    {
        $row = $this->fetchRow(array(
            'customer_activation_code' => 'userId',
            'customer_pin' => $pin
        ));

        if(!$row) {
            return null;
        }

        return $row;
    }

    public function authenticateByDevicePinDeviceId($dpin, $cpin, $did, $cid)
    {
        if(is_null($dpin) || is_null($cpin) || is_null($did) || is_null($cid)) {
            return false;      
        }

        $resultSet = $this->select(function(Select $select){
            $select->from(array('c' => 'customer'));
            $select->join(
                array('d' => 'device'), 
                'd.device_cust_id = c.customer_id',
                array(), $select::JOIN_INNER
            );
            $select->where(array(
                "c.customer_pin = $cpin"
            ));
                   //->nest()
                   //->equalto('c.customer_pin  = ?', $cpin)
                   //->and()
                   //->equalto('c.customer_activation_code  = ?', $dpin)
                   //->and()
                   //->equalto('d.device_vutv_device_id = ?', $did)
                   //->and()
                   //->equalto('c.customer_company_id  = ?', $cid);
        });

        return $resultSet; 
    }

    public function save(Customer $customer)
    {
        $data = $customer->getArrayCopy();

        $id = (int) $customer->customer_id;

        $lastInsert = null;
        if ($id == 0) {
            $this->insert($data);
            $lastInsert = $this->getLastInsertValue();
        } elseif ($this->getCustomerById($id)) {
            $this->update(
                $data,
                array(
                    'customer_id' => $id,
                )
            );
            $user = $this->getUserById($id);
            $lastInsert = $user->getArrayCopy();
        } else {
            throw new \Exception('Unable to insert or update record');
        }

        return $lastInsert;
    }
}

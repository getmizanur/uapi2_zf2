<?php

namespace Synapse\Model\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\InputFilter\Factory;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

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

        $resultSet = $this->select(function(Select $select) 
                use($cpin, $dpin, $did, $cid)  {
            $select->join('device', 
                        'device.device_cust_id = customer.customer_id',
                        array(), $select::JOIN_INNER
                    );
            $select->where(
                array(
                    'customer.customer_pin' => $cpin,
                    'customer.customer_activation_code' => $dpin,
                    'device.device_vutv_device_id' => $did,
                    'customer.customer_company_id' => $cid
                ),
                \Zend\Db\Sql\Predicate\PredicateSet::OP_AND
            );
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

<?php

namespace Synapse\Model\Table;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\InputFilter\Factory;
use Zend\Db\Sql\Select;

use Synapse\Model\Entity\Device;

class DeviceTable extends AbstractTable 
{
    protected $table;
    protected $tableName;

    public function __construct($table, Adapter $dbAdapter) 
    {
        $this->table = $this->tableName = $table;
        $this->adapter = $dbAdapter;

        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Device());

        $this->initialize();
    }

    public function getDeviceById($id) 
    {
       $row = $this->fetchRow(array(
            'device_id' => $id
        ));
    
        if(!$row) {
            return new \ArrayObject();
        }

        return $row;  
    }    

    public function getDeviceByCustomerId($custId)
    {
        $row = $this->fetchRow(array(
            'device_cust_id' => $custId
        ));
    
        if(!$row) {
            return null;
        }

        return $row;    
    }  

    public function save(Device $device)
    {
        $data = $device->getArrayCopy();

        $id = (int) $device->device_id;

        $lastInsert = null;
        if ($id == 0) {
            $this->insert($data);
            $lastInsert = $this->getLastInsertValue();
        } elseif ($this->getDeviceById($id)) {
            $this->update(
                $data,
                array(
                    'device_id' => $id,
                )
            );
            $user = $this->getUserById($id);
            $lastInsert = $user->getArrayCopy();
        } else {
            throw new InvalidException('Unable to insert or update record');
        }

        return $lastInsert;
    }
}

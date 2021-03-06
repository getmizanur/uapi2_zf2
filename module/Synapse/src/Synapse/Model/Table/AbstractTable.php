<?php

namespace Synapse\Model\Table;

use Zend\Db\TableGateway\AbstractTableGateway;

class AbstractTable extends AbstractTableGateway
{    
    public function fetchAll() 
    {
        return $this->select();
    }
    
    public function fetchRow($where)
    {   
        $rowset = $this->select($where);
        return $rowset->current();      
    } 
} 


<?php

namespace Synapse\Model\Table;

use Zend\Db\Adapter\Adapter;

class AuditTable extends AbstractTable 
{
    protected $table;
    protected $tableName;

    public function __construct($table, Adapter $dbAdapter) 
    {
        $this->table = $this->tableName = $table;
        $this->adapter = $dbAdapter;

        $this->initialize();
    }

    public function getAuditById($id) 
    {
        $select = $this->getSql()->select()
                       ->where(array('audit_id' => $id));

        return $this->selectWith($select);
    }    
}

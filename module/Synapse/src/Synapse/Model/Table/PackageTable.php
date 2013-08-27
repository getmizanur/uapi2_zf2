<?php

namespace Synapse\Model\Table;

use Zend\Db\Adapter\Adapter;

class PackageTable extends AbstractTable 
{
    protected $table;
    protected $tableName;

    public function __construct($table, Adapter $dbAdapter) 
    {
        $this->table = $this->tableName = $table;
        $this->adapter = $dbAdapter;

        $this->initialize();
    }

    public function getPackageById($id) 
    {
        $select = $this->getSql()->select()
                       ->where(array('package_id' => $id));

        return $this->selectWith($select);
    }    
}

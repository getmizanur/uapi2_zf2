<?php
return array(
    'db_adapter_manager' => array(
        'synapse' => array (
            'catchuptv' => array(
                'driver' => 'Pdo',
                'dsn' => 'mysql:dbname=catchuptv;host=mm-phoenix.c1mmeweekpwh.eu-west-1.rds.amazonaws.com',
                'username' => 'root',
                'password' => 'c63tAZ94',
            ),
            'simplstream_clients' => array(
                'driver' => 'Pdo',
                'dsn' => 'mysql:dbname=simplestream_clients;host=mm-phoenix.c1mmeweekpwh.eu-west-1.rds.amazonaws.com',
                'username' => 'root',
                'password' => 'c63tAZ94',
            ),
            'customers' => array(
                'driver' => 'Pdo',
                'dsn' => 'mysql:dbname=customers;host=mm-phoenix.c1mmeweekpwh.eu-west-1.rds.amazonaws.com',
                'username' => 'root',
                'password' => 'c63tAZ94',
            ),
        ),
    ),
);

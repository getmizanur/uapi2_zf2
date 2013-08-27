<?php

namespace ModuleTest\Synapse\Entity;

use PHPUnit_Framework_TestCase;
use Synapse\Model\Entity\Device;

class DeviceEntityTest extends PHPUnit_Framework_TestCase
{    
    public function testEntity()
    {
        $device = new Device();
        $device->device_id = 1;
        $device->device_name = 'test';
        $device->device_vutv_device_id = 1;
        $device->device_cust_id = 1;
        $device->device_active = true;
        $device->device_activation = '2013-08-27';

        $this->assertEquals($device->device_id, 1);
        $this->assertEquals($device->device_name, 'test');
        $this->assertEquals($device->device_vutv_device_id, 1);
        $this->assertEquals($device->device_cust_id, 1);
        $this->assertEquals($device->device_active, true);
        $this->assertEquals($device->device_activation, '2013-08-27');

        $deviceArr = $device->getArrayCopy();
        $this->assertEquals($deviceArr['device_activation'], '2013-08-27');

        if($device->isValid()) {
            $this->assertTrue(true);
        }else{
            $this->assertTrue(false);
        }
    }
} 

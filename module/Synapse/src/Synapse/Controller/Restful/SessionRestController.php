<?php

namespace Synapse\Controller\Restful;

use Synapse\Controller\Restful\AbstractRestfulController;
use Synapse\Model\Entity\Device;

use Zend\View\Model\JsonModel;
use Zend\InputFilter\Factory;
use Zend\Session\Container;

class SessionRestController extends AbstractRestfulController
{    
    public function getList()
    {
        $response = array();

        $sm = $this->getServiceLocator();
        $match = $sm->get('Application')
                    ->getMvcEvent()
                    ->getRouteMatch();

        $factory = new Factory();
        $inputFilter = $factory->createInputFilter(array (
            'userid' => array (
                'name' => 'text',
                'required' => true,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'not_empty',
                        'options' => array(
                            'message' => "User ID cannot be empty"
                        )
                    ),
                    array (
                        'name' => 'int',
                        'options' => array(
                            'message' => "User ID is an integer value"
                        )
                    )
                )            
            ),   
            'deviceid' => array (
                'name' => 'text',
                'required' => true,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'not_empty',
                        'options' => array(
                            'message' => "Device ID cannot be empty"
                        )
                    ),
                    array (
                        'name' => 'alnum',
                        'options' => array(
                            'message' => "Device ID is an alnum"
                        )

                    )
                )
            ),
            'pin' => array (
                'name' => 'text',
                'required' => true,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'not_empty',
                        'options' => array(
                            'message' => "User pin cannot be empty"
                        )
                    ),
                    array (
                        'name' => 'int',
                        'options' => array(
                            'message' => "User pin is an integer value"
                        )

                    )
                )
            ),
            'session' => array (
                'name' => 'text',
                'required' => true,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'not_empty',
                        'options' => array(
                            'message' => "Session cannot be empty"
                        )
                    ),
                    array (
                        'name' => 'alnum',
                        'options' => array(
                            'message' => "Invalid session"
                        )

                    )
                )
            ),
            'companyid' => array (
                'name' => 'text',
                'required' => true,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'not_empty',
                        'options' => array(
                            'message' => "Company ID cannot be empty"
                        )
                    ),
                    array (
                        'name' => 'int',
                        'options' => array(
                            'message' => "Company ID is an integer value"
                        )

                    )
                )
            ),
            'verbos' => array (
                'name' => 'text',
                'required' => false,
                'filters' => array (
                    array ('name' => 'StripTags'),
                    array ('name' => 'StringTrim'),
                    array ('name' => 'HtmlEntities')
                ),
                'validators' => array (
                    array (
                        'name' => 'in_array',
                        'options' => array (
                            'haystack' => array(
                                'y', 'n'
                            )
                        )
                    ),
                )
            ),
        ));

        $inputFilter->setData($this->getRequest()->getQuery());

        if($inputFilter->isValid()) {
            $result = $sm->get('CustomerModel')->authenticateByDevicePinDeviceId(
                $inputFilter->getValue('userid'),
                $inputFilter->getValue('pin'),
                $inputFilter->getValue('deviceid'),
                $inputFilter->getValue('companyid')
            );

            if(count($result) > 1) {
                $deviceResultSet = $sm->get('DeviceModel')->getDeviceByCustomerId(
                    $result->customer_id
                );

                if(!$deviceResultSet){
                    $device = new Device();
                    $device->device_name = "test";
                    $device->device_cust_id = $result['customer_id'];
                    $device->device_vutv_device_id = uniqid();
                    $device->device_active = 1;
                    $device->device_activation = date('Y-m-s H:i:s');

                    $id = $sm->get('DeviceModel')->save($device);
                    
                    $response = array (                 
                        'synapse' => array (                
                            'deviceId' => $device->device_vutv_device_id, 
                            'reportingId' => "1-2-3"
                        )
                    );
                    $this->plugin('flashmessenger')->addValidMessage(
                        'Success'
                    );
                }else{
                    $response = array (                 
                        'synapse' => array (                
                            'error' => 2,               
                        )
                    );
                    $this->plugin('flashmessenger')->addErrorMessage(
                        'A device is associated to this userid'
                    );
                }
            }else{
                $response = array (                 
                    'synapse' => array (                
                        'error' => 1,               
                    ) 
                );           
                $this->plugin('flashmessenger')->addErrorMessage(
                    'User ID not found'
                );
            }
        }else{
            $response = array (
                'synapse' => array (                
                    'error' => 3,               
                )
            );
            foreach($inputFilter->getInvalidInput() as $error) {
                $this->plugin('flashmessenger')
                    ->addErrorMessage($error->getMessages());
            }
        }
        
        $this->_model->setVariables($response);

        return $this->_model;
    }  

    public function get($id)
    {
        if(!$id) {
            $this->getList();
        }

        return $this->_model;
    }

    public function create($data)
    {
        return $this->_model;
    }

    public function update($id, $data)
    {
        return $this->_model;
    }

    public function delete($id)
    {
        return $this->_model;
    }
}

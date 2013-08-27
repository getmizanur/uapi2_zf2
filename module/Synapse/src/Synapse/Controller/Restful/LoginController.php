<?php

namespace Synapse\Controller\Restful;

use Synapse\Controller\Restful\AbstractRestfulController;

use Zend\View\Model\JsonModel;
use Zend\InputFilter\Factory;
use Zend\Session\Container;

class LoginController extends AbstractRestfulController
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
                            'message' => "Device ID is an alnum value"
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
            $sm->get('AuthService')
               ->getAdapter()
               ->setIdentity($inputFilter->getValue('userid'))
               ->setCredential($inputFilter->getValue('pin'))
               ->getDbSelect()
               ->where('customer_company_id =' . 
                    $inputFilter->getValue('companyid'));
                 
            $result = $sm->get('AuthService')->authenticate();

            if($result->isValid()) {
                $sm->get('AuthService')->getStorage()->write(
                    $inputFilter->getValue('userid')
                );

                session_regenerate_id();

                $session = new Container('synapse');
                $session->session_id = session_id;
                $session->userid = $inputFilter->getValue('userid');
                $session->companyid = $inputFilter->getValue('companyid');
                $session->deviceid = $inputFilter->getValue('deviceid');

                $response = array(
                    'synapse' => array(
                        'session' => session_id(),
                        'contentlength' => 0,
                        'userstatus' => 'active'
                    )
                );
                $this->plugin('flashmessenger')->addValidMessage("valid message");
            }else{
                $response = array (                 
                    'synapse' => array (                
                        'error' => 1,               
                    ) 
                );           
            }
        }else{
            $response = array (
                'synapse' => array (                
                    'error' => 3,               
                )
            );
            if(0 == strcmp($inputFilter->getValue('verbos'), 'y')) {
                $errorMessage = array();
                foreach($inputFilter->getInvalidInput() as $error) {
                    $this->plugin('flashmessenger')
                        ->addErrorMessage($error->getMessages());
                }
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

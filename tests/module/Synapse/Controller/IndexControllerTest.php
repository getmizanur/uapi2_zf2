<?php

namespace ModuleTest\Synapse\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{    
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../config/application.config.php'
        );
        parent::setUp();
    }
    
    public function testCanDisplayIndex()
    {
        // dispatch url
        $this->dispatch('/');
        
        // basic assertions
        //$this->assertResponseStatusCode(200);
        $this->assertActionName('index');
        $this->assertControllerName('synapse-index');
        $this->assertMatchedRouteName('home');
        $this->assertQuery('div[class="container"]');
        $this->assertNotQuery('#form');
        $this->assertQueryCount('div[class="container"]', 2);
        
        // custom assert
        $sm = $this->getApplicationServiceLocator();
        // ... here my asserts ...
    }
}

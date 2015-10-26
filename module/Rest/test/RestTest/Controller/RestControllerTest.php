<?php

namespace RestTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class RestControllerTest extends AbstractHttpControllerTestCase{
    
    protected $traceError = true;

    
    public function setUp(){
        $this->setApplicationConfig(
            include '/var/www/REST/config/application.config.php'
        );
        parent::setUp();
    }
    
    private function dbMock($method){

            $mock = $this->getMockBuilder('Rest\Model\CarsTable')
                            ->disableOriginalConstructor()
                            ->getMock();
            if($method == 'GET'){
                $mock->expects($this->once())
                        ->method('read')
                        ->will($this->returnValue(array("id" => "1", "name" => "ferrari", "year" => "2015", "price" => "350000.00")));
            }
            if($method == 'POST' || $method == 'PUT'){
                $mock->expects($this->once())
                        ->method('write')
                        ->will($this->returnValue(null));
            }        
            if($method == 'DELETE'){
                $mock->expects($this->once())
                        ->method('delete')
                        ->will($this->returnValue(null));
            }
            $sm = $this->getApplicationServiceLocator();
            $sm->setAllowOverride(true);
            $sm->setService('Rest\Model\CarsTable', $mock);

        
    }
    
    private function authMock($flag){
        $mock = $this->getMockBuilder('Rest\Model\Auth')
                            ->disableOriginalConstructor()
                            ->getMock();
        $mock->expects($this->once())
                        ->method('basic')
                        ->will($this->returnValue($flag));
        $sm = $this->getApplicationServiceLocator();
        $sm->setAllowOverride(true);
        $sm->setService('Rest\Model\Auth', $mock);
    }
    
    public function testGetJsonSuccess(){
        $this->dbMock("GET");
        $this->dispatch('/rest/cars/1');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Rest');
        $this->assertResponseHeaderContains("Content-Type","application/json");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"year") !== false);
    }
    public function testPostJsonAuth(){
        $this->dbMock("POST");
        $this->authMock(true);
        
        $post = '{"name":"Ferrari","year":"2015","price":"350000"}';
        $this->getRequest()
                ->setMethod('POST')->setContent($post);
        $this->dispatch('/rest/cars');
        $this->assertResponseStatusCode(201);
        $this->assertResponseHeaderContains("Content-Type","application/json");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"has been created") !== false);
        
    }
    public function testPostJsonNoAuth(){
        $this->authMock(false);
        $post = '{"name":"Ferrari","year":"2015","price":"350000"}';
        $this->getRequest()
                ->setMethod('POST')->setContent($post);
        $this->dispatch('/rest/cars');
        $this->assertResponseStatusCode(401);
        $this->assertResponseHeaderContains("Content-Type","application/json");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"Authentication needed") !== false);     
    }
    public function testPostJsonAuthInvalidData(){
        $this->authMock(true);
        $post = '{"name":"Fe&%rrari","year":"2015","price":"35000/0"}';
        $this->getRequest()
                ->setMethod('POST')->setContent($post);
        $this->dispatch('/rest/cars');
        $this->assertResponseStatusCode(400);
        $this->assertResponseHeaderContains("Content-Type","application/json");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"Invalid data format for the resource") !== false);
        
    }
    public function testPostJsonAuthNoData(){
        $this->authMock(true);
        $post = '';
        $this->getRequest()
                ->setMethod('POST')->setContent($post);
        $this->dispatch('/rest/cars');
        $this->assertResponseStatusCode(400);
        $this->assertResponseHeaderContains("Content-Type","application/json");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"No content specified") !== false);
        
    }
    
    public function testPutXmlAuth(){
        $this->dbMock("PUT");
        $this->authMock(true);
        $post = '<resource><id>1</id><name>Ferrari</name><year>2015</year><price>350000</price></resource>';
        $this->getRequest()
                ->setMethod('PUT')->setContent($post);
        $this->dispatch('/rest/cars/1/xml');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"has been updated") !== false);
        
    }
    public function testPutXmlNoAuth(){
        $this->authMock(false);
        $post = '<resource><id>1</id><name>Ferrari</name><year>2015</year><price>350000</price></resource>';
        $this->getRequest()
                ->setMethod('PUT')->setContent($post);
        $this->dispatch('/rest/cars/1/xml');
        $this->assertResponseStatusCode(401);
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"Authentication needed") !== false);     
    }
    public function testPutXmlAuthInvalidData(){
        $this->authMock(true);
        $post = '<resource><idasd>1</id><name>Ferrari</name><yedar>2015</year><price>35w0000</price></resource>';
        $this->getRequest()
                ->setMethod('PUT')->setContent($post);
        $this->dispatch('/rest/cars/1/xml');
        $this->assertResponseStatusCode(400);
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"Invalid XML data.") !== false);
        
    }
    public function testPutXmlAuthNoData(){
        $this->authMock(true);
        $post = '';
        $this->getRequest()
                ->setMethod('PUT')->setContent($post);
        $this->dispatch('/rest/cars/1/xml');
        $this->assertResponseStatusCode(400);
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"No content specified") !== false);
        
    }
    
    public function testDeleteXMLSuccess(){
        $this->authMock(true);
        $this->dbMock("DELETE");
        $this->getRequest()
                ->setMethod('DELETE');
        $this->dispatch('/rest/cars/1/xml');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Rest');
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"has been deleted") !== false);
    }
    public function testDeleteXMLFailure(){
        $this->authMock(true);
        $this->getRequest()
                ->setMethod('DELETE');
        $this->dispatch('/rest/cars/xml');
        $this->assertResponseStatusCode(400);
        $this->assertModuleName('Rest');
        $this->assertResponseHeaderContains("Content-Type","application/xml");
        $this->assertEquals(true,strpos($this->getResponse()->getContent(),"Resource id is required") !== false);
    }
}
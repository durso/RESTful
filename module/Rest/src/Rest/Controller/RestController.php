<?php

namespace Rest\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Response;

class RestController extends AbstractActionController{
     
    private $tables;
    private $auth;
    protected $ext;
    protected $code = 200;
     
    public function indexAction(){
        $this->ext = $this->getEvent()->getRouteMatch()->getParam('type');
        $this->ext = $this->ext ? $this->ext : 'json';
        try{
            $restServer = $this->getRestServer();
            $content = $restServer->getResponse($this->ext);
            return $this->response($content);
        } catch (\Exception $ex) {
            if($this->code == 200){
                $this->code = 500;
            }
            return $this->error($ex->getMessage());
        }
    }

    private function getRestServer(){
        $tableName = ucfirst(strtolower($this->getEvent()->getRouteMatch()->getParam('collection')));
        //just add the table name in the array below (don't forget to add the new collection on the router)
        if(!in_array($tableName, array("Cars"))){
            throw new \Exception("Invalid Collection name");
        }
        $restServer = new \Rest\Model\Rest($this->getTable($tableName));
        $method = $this->getRequest()->getMethod();
        switch($method){
           case 'GET':     $this->readAction($restServer);
                           break;
           case 'POST':    
           case 'PUT':     $this->writeAction($restServer);
                           break;
           case 'DELETE':  $this->deleteAction($restServer);
                           break;
           default:        $this->code = 405; 
                           throw new \Exception(sprintf("Method %s not supported",$method));    
       }
       return $restServer;
    }

    private function readAction($restServer){
       $resource = $this->getEvent()->getRouteMatch()->getParam('resource');
       $restServer->read($resource);
    }

    private function deleteAction($restServer){
       $this->auth();
       $resource = $this->getEvent()->getRouteMatch()->getParam('resource');
       if(is_null($resource)){
           $this->code = 400;
           throw new \Exception("Resource id is required");  
       }
       $restServer->delete($resource);
    }

    private function writeAction($restServer){
       $this->auth();
       $request = $this->getRequest();
       if($this->ext == null){
           $ext = 'json';
       }
       
       $resource = $this->readContent($request);
       
       $this->validate($resource);

       if($request->isPost()){
           $restServer->create($resource);
           $this->code = 201;
       } else {
           $restServer->update($resource);
       }
    }

    private function readContent($request){
        $content = $request->getContent();
        
        if(empty($content)){
            $this->code = 400;
            throw new \Exception("No content specified.");
        }
        
        if($this->ext == 'xml'){ 
            $xml = @simplexml_load_string($content);
            
            if($xml !== false){
                $content = json_encode($xml);
            } else {
                $this->code = 400;
                throw new \Exception("Invalid XML data.");
            }        
        }
        
        return json_decode($content,true);
    }
    
    private function getTable($tableName){
         if (!$this->tables[$tableName]) {
             $sl = $this->getServiceLocator();
             $this->tables[$tableName] = $sl->get('Rest\Model\\'.$tableName.'Table');
         }
         return $this->tables[$tableName];
    }
    
    private function getAuth(){
         if (!$this->auth) {
             $sl = $this->getServiceLocator();
             $this->auth = $sl->get('Rest\Model\Auth');
         }
         return $this->auth;
    }
    
    private function auth(){
        $auth = $this->getAuth();
        $isValid = $auth->basic($this->getRequest()); 
        if(!$isValid){
            $this->code = 401;
            $this->getResponse()->getHeaders()->addHeaderLine('WWW-Authenticate',  "Basic realm=\"".base64_encode("Rest")."\"");
            throw new \Exception("Authentication needed");
        }
        
    }

    private function validate($resource){
        $error = false;
        if(!$this->getRequest()->isPost()){
            $validatorId = new \Zend\Validator\Digits();
            $id = $this->getEvent()->getRouteMatch()->getParam('resource');
            if(!$validatorId->isValid($resource['id']) || $id != $resource['id']){
                $error = true;
            }
        } else {
            $error = array_key_exists("id", $resource);
        }
        $validatorName = new \Zend\I18n\Validator\Alnum(array('allowWhiteSpace' => true));
        if(!$validatorName->isValid($resource['name']) && !$error){
            $error = true;
        }
        $validatorYear = new \Zend\Validator\Date(array('format' => 'Y'));
        if(!$validatorYear->isValid($resource['year']) && !$error){
            $error = true;
        }
        $validatorPrice = new \Zend\I18n\Validator\IsFloat();
        if(!$validatorPrice->isValid($resource['price']) && !$error){
            $error = true;
        }
        if($error){
            $this->code = 400;
            throw new \Exception("Invalid data format for the resource");
        }
    }

    
    private function response($content){
        $response = $this->getResponse();
        $header = $response->getHeaders();
        $header->addHeaderLine('Content-Type', sprintf('application/%s',$this->ext));
        $response->setStatusCode($this->getCode());
        $response->setContent($content);
        return $response;
    }
    

    private function error($msg){
        if($this->ext == 'json'){
            $content = json_encode(array("error" => array("msg" => $msg)));
        } else {
            $xml = new \SimpleXMLElement('<error/>');
            $xml->addChild("msg",$msg);
            $content = $xml->asXML();
        }
        return $this->response($content);
    }
    
    private function getCode(){
        switch($this->code){
            case 500: return Response::STATUS_CODE_500;
            case 405: return Response::STATUS_CODE_405;
            case 401: return Response::STATUS_CODE_401;
            case 400: return Response::STATUS_CODE_400;
            case 201: return Response::STATUS_CODE_201;
            case 200: return Response::STATUS_CODE_200;
            default:  return Response::STATUS_CODE_404;
        }
    }


 }
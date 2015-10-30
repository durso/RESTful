<?php

namespace Rest\Model;

 class Rest
 {

    private $response;
    private $collection;
    private $rootXML = 'resource';
    
    public function __construct(Collection $collection){
        $this->collection = $collection;
    }
  
    public function create($resource){
        $this->collection->write($resource);
        $this->response = array("msg" => "The resource has been created");
    }
    public function read($resource){
        if(is_null($resource)){
            $this->response = $this->collection->getCollection();
            $this->rootXML = 'collection';
        } else{
            $this->response = $this->collection->read($resource);
        }
        
    }
    public function update($resource){
        $this->collection->write($resource);
        $this->response = array("msg" => sprintf("The resource #%d has been updated",$resource['id']));
    }
    public function delete($resource){
        $this->collection->delete($resource);
        $this->response = array("msg" => sprintf("The resource #%d has been deleted",$resource));
    }
    public function getResponse($ext){
        if($ext == 'json'){
            return $this->getJson();
        }
        return $this->getXML();
    }
    public function getJson(){
        return json_encode($this->response);
    }
    public function getXML(){
        $xml = new \SimpleXMLElement("<".$this->rootXML."/>");
        foreach($this->response as $key => $value){
            if(!is_string($value)){
                $resource = $xml->addChild("resource");
                foreach($value as $childkey => $childvalue){
                    $resource->addChild($childkey, $childvalue);
                }
            } else {
                $xml->addChild($key, $value);
            }
        }
        return $xml->asXML();
    }
 }
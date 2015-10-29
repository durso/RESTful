<?php

namespace Rest\Model;

use Zend\Db\TableGateway\TableGateway as gateway;

class Collection{
    
    protected $gateway;
    protected $collection;

    public function __construct(gateway $gateway){
        $this->gateway = $gateway;
    }
    
    public function getCollection(){
         $rows = $this->gateway->select();
         $result = array();
         foreach($rows as $row){
             $result[] = $row;
         }
         return $result;
    }
    public function read($id){
        $result = $this->gateway->select(array('id' => $id));
        $row = $result->current();
        if (!$row){
            throw new \Exception("Could not find row #$id");
        }
        return $row;
    }

    public function write($resource){
        if(!array_key_exists('id', $resource)){
            $this->gateway->insert($resource);
        }else {
            $id = $resource['id'];
            if($this->read($id)){
                $this->gateway->update($resource, array('id' => $id));
            } else {
                throw new \Exception(sprintf('Row #%d does not exist',$id));
            }
        }
    }

    public function delete($id){
        $this->gateway->delete(array('id' => $id));
    }
}
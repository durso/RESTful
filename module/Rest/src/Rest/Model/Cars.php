<?php
namespace Rest\Model;

class Cars extends Resource{
     
    public $id;
    public $name;
    public $year;
    public $price;

    public function exchangeArray($resource){
        $this->id = (!empty($resource['id'])) ? $resource['id'] : null;
        $this->name = (!empty($resource['name'])) ? $resource['name'] : null;
        $this->year = (!empty($resource['year'])) ? $resource['year'] : null;
        $this->price = (!empty($resource['price'])) ? $resource['price'] : null;
    }
    
    public function getId(){
        return $this->id;
    }
   
}
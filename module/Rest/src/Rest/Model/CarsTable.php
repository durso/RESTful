<?php

namespace Rest\Model;
use Zend\Db\TableGateway\TableGateway as gateway;

class CarsTable extends Collection{
    public function __construct(gateway $gateway){
        parent::__construct($gateway);
    }
}
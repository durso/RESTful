<?php


namespace Rest\Model;


class Auth{
    
    public function basic($request){
        $client["rod"] = "xCx456";
        $server = $request->getServer();
        $username = $server->get('PHP_AUTH_USER');
        $pw = $server->get('PHP_AUTH_PW');
        return $username && array_key_exists($username, $client) && $client[$username] == $pw;
    }
    
    public function OAuth(){
        
    }
    
    public function HMAC(){
        
    }
}
<?php 
//application/libraries/CreatorJwt.php
    require APPPATH . '/libraries/JWT.php';
    class CreatorJwt {
        /*************This function generate token private key**************/ 
        // PRIVATE $key = "1234567890qwertyuiopmnbvcxzasdfghjkl";
        public function GenerateToken($data, $key) {          
            $jwt = JWT::encode($data, $key);
            return $jwt;
        }
        
       /*************This function DecodeToken token **************/
        public function DecodeToken($token, $key) {          
            $decoded = JWT::decode($token, $key, array('HS256'));
            $decodedData = (array) $decoded;
            return $decodedData;
        }
    }
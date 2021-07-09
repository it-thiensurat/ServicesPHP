<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Api_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->apiKey = $this->config->item('key_token');
    }

    public function checkApiKey($key) {
        if ($this->apiKey === $key) {
            return true;
        } else {
            return false;
        }
    }
}

?>
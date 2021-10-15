<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetCustomerCH extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $sql = "SELECT * FROM TSR_Application.dbo.CUSTOMER_CHANNEL WHERE CUSTOMER_CH_STATUS = 1";
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status"    => "SUCCESS",
                "message"   => " ช่องทางการติดต่อลูกค้า",
                "data"      => $stmt->result_array()
            ), 200
        );
    }
}
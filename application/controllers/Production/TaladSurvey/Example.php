<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Example extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
		$this->load->database();
	}
    public function index_get() {
        $sql = "select * from  [TSR_DB1].[dbo].[TRANSECTION_PAYMENT]";
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status" => "success",
                "message" => "testapi",
                "data" => $stmt->result_array()
            )
            , 200
        );
    }
 
    public function index_post()
    {
       
    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {
        
    }
}
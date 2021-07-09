<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class TestApi extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        $contract = '70147113';
        $contract2 = '31501161';
        $sql = "SELECT * FROM [TSR_DB1].dbo.V_Contract_Master
                WHERE ContractNo IN (?, ?) ORDER BY Effdate DESC";
        $stmt = $this->db->query($sql, array($contract, $contract2));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "testapi",
                    "data"      => $stmt->result_array()
                )
                , 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "testapi",
                    "data"      => ''
                )
                , 200
            );
        }
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
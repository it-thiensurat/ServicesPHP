<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetCustomerDetail extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
    }

    public function index_post() {
        $customerId  = $this->input->post('customerId');
        $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.ALPINE_QUOTATION WHERE APCUS_ID = ? ORDER BY APO_ID DESC";
        $stmt = $this->db->query($sql, array($customerId));
        
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $res = array(

                );

                array_push($data, $res);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายบะเอียด",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูล",
                    "data"      => ''
                ), 200
            );
        }
    }
}
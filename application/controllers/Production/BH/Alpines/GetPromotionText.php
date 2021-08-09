<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetPromotionText extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $sql = "SELECT * FROM TSRData_Source.dbo.BigHead_AlpinePromotion";
        $stmt = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายละเอียดโปรโมชั่น",
                    "data"      => $stmt->result_array()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลโปรโมชั่น",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function index_post() {
        
    }
}
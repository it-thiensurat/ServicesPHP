<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetProduct extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        
    }

    public function index_post() {
        $product_id = $this->input->post('productId');
        $sql = "SELECT * FROM SQLUAT.TSRData_Source.dbo.BigHead_AlpineProductSpec WHERE ProductID = ?";
        $stmt = $this->db->query($sql, array($product_id));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการสินค้า",
                    "data"      => $stmt->result_array()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลสินค้า",
                    "data"      => $product_id
                ), 200
            );
        }
    }
}
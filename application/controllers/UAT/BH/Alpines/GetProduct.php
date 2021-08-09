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
        $customerId = $this->input->post('customerId');
        $sql        = "SELECT * FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER AS ac WHERE ac.APCUS_ID = ? AND ac.APCUS_STATUS = ?";
        $stmt       = $this->db->query($sql, array($customerId, 0));
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

    public function getQuotationId($customerId) {
        $sql = "SELECT * FROM SQLUAT.TSR_DB1.dbo.ALPINE_QUOTATION WHERE ";
        $stmt = $this->db->query($sql, array($customerId));
        if ($stmt->num_rows() > 0) {

        }
    }
}
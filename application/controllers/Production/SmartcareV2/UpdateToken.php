<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class UpdateToken extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $customerID     = $this->input->post('customer_id');
        $token          = $this->input->post('token');
        $updatedby      = 'SMARTCARE';
        $updateddate    = date('Y-m-d H:i:s');

        $sql        = "UPDATE TSR_DB1.dbo.CUSTOMER_INFO SET FirebaseToken = ?, UpdatedBy = ?, UpdatedDate = ? WHERE CustomerId = ?";
        $stmt       = $this->db->query($sql, array($token, $updatedby, $updateddate, $customerID));
        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "อัพเดทโทเค่นแล้ว",
                    "data"      => ''
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถอัพเดทโทเค่นได้",
                    "data"      => ''
                ), 200
            );
        }
    }
}
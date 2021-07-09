<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

class GetActivity extends REST_Controller { 

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
    
    }

    public function index_post() {
        $empId  = $this->input->post('empid');

        $sql    = "EXEC TSR_Application.dbo.CRD_ActCode_List @empid = ?";
        $stmt   = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get activity successfull.",
                    "data"      => $result
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Get activity failed.",
                    "data"      => ""
                ), 200
            );
        }
    }
}
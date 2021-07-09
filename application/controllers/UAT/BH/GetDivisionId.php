<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetDivisionId extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $empid      = $this->input->post('empid');
        $sql        = "SELECT departid FROM SQLUAT.TSR_Application.dbo.TSR_Full_EmployeeLogic
                        WHERE empid = ? AND status =1";
        $stmt       = $this->db->query($sql, array($empid));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการตรวจสอบ",
                    "data"      => $stmt->row()->departid
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูล",
                    "data"      => []
                ), 200
            );
        }
    }
}
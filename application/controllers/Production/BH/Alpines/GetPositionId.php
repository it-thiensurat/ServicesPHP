<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetPositionId extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        $empid      = $this->input->get('empid');
        $sql        = "SELECT positid FROM TSR_Application.dbo.TSR_Full_EmployeeLogic WHERE empid = ? AND status =1";
        $stmt       = $this->db->query($sql, array($empid));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการตรวจสอบ",
                    "data"      => $stmt->row()->positid
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

    public function index_post() {
        $empid      = $this->input->post('empid');
        $sql        = "SELECT positid FROM TSR_Application.dbo.TSR_Full_EmployeeLogic WHERE empid = ? AND status =1";
        $stmt       = $this->db->query($sql, array($empid));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการตรวจสอบ",
                    "data"      => $stmt->row()->positid
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
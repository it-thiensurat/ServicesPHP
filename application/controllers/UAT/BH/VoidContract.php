<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class VoidContract extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $refno = $this->input->post('refno');
        $empid = $this->input->post('empid');

        $sql = "UPDATE SQLUAT.TSR_Application.dbo.ApproveContno SET Approve = 4
                WHERE RefNo = ? AND createby = ?";
        $stmt = $this->db->query($sql, array($refno, $empid));
        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "ยกเลิกรายการตรวจสอบ",
                    "data"      => ""
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มียกเลิกการตรวจสอบได้",
                    "data"      => []
                ), 200
            );
        }
    }
}
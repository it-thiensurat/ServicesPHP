<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class ApproveContno extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $refno      = $this->input->post('refno');
        $empid      = $this->input->post('empid');
        $salecode   = $this->input->post('salecode');
        $empid4     = $this->input->post('empid4');
        $posid4     = $this->input->post('posid4');
        $empid5     = $this->input->post('empid5');
        $posid5     = $this->input->post('posid5');
        $empid6     = $this->input->post('empid6');
        $posid6     = $this->input->post('posid6');
        $teamcode   = $this->input->post('teamcode');

        $sql = "exec TSR_Application.[dbo].sApproveContno 
                @RefNo = ?, @createby = ?, @Empid4 = ?, @PosId4 = ?, @Empid5 = ?, @PosId5 = ?, @Empid6 = ?, @PosId6 = ?, @TeamCode = ?, @salecode = ?";
        $stmt = $this->db->query($sql, array($refno, $empid, $empid4, $posid4, $empid5, $posid5, $empid6, $posid6, $teamcode, $salecode));
        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Approve contno",
                    "data"      => "",
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบรายการ",
                    "data"      => ""
                ), 200
            );
        }
    }
}
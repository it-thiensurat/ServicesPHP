<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class HelpdeskInprogress extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {

    }

    public function index_post() {
        $contno = $this->input->post('contno');
        $sql = "SELECT PM.InformID, PD.ProblemDetail, PW.WorkName FROM TSR_ONLINE_MARKETING.dbo.Problem_Inform_Master AS PM
                LEFT JOIN TSR_ONLINE_MARKETING.dbo.Problem_Inform_Details AS PD ON PM.InformID = PD.InformID
                LEFT JOIN TSR_ONLINE_MARKETING.dbo.Problem_WorkCode AS PW ON PM.WorkCode = PW.WorkCode
                WHERE PM.Contno = ? AND PM.DataChannel = ? ORDER BY PM.InfromDateTime DESC";
                
        $stmt = $this->db->query($sql, array($contno, '05'));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get ProblemInprogress",
                    "data"      => $stmt->result_array()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถดึงข้อมูลสัญญาได้",
                    "data"      => ''
                ), 200
            );
        }
    }
}
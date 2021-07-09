<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

class AddProcess extends REST_Controller { 

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
    
    }

    public function index_post() {
        
        
        $fid        = $this->input->post('fid');
        $assignId   = $this->input->post('assignid');
        $refno      = $this->input->post('refno');
        $contno     = $this->input->post('contno');
        $activity   = $this->input->post('activity');
        $remark     = $this->input->post('remark');
        $empId      = $this->input->post('empid');
        $adddate    = $this->input->post('adddate');

        // $this->response(
        //     array(
        //         "status"    => "SUCCESS",
        //         "message"   => "Get activity successfull.",
        //         "data"      => $this->input->post()
        //     ), 200
        // );

        $sql    = "EXEC TSR_Application.dbo.CRD_Table_Files_UpResult @f_id = ?, @assign_id = ?, @refno = ?,
                @contno = ?, @actid = ?, @remark = ?, @result_by = ?, @app_date = ?";
        $stmt   = $this->db->query($sql, array($fid, $assignId, $refno, $contno, $activity, $remark, $empId, $adddate));
        // print_r($stmt);
        if ($stmt) {
            // $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "บันทึกผลการดำเนินงานแล้ว",
                    "data"      => ""
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถบันทึกได้",
                    "data"      => ""
                ), 200
            );
        }
    }
}
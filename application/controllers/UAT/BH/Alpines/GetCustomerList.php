<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetCustomerList extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $empId  = $this->input->post('empId');
        if ($empId == "") {
            $sql    = "SELECT *, (SELECT TOP 1 aq.APQ_ID FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER ac 
                    LEFT JOIN SQLUAT.TSR_DB1.dbo.ALPINE_QUOTATION aq ON ac.APCUS_ID = aq.APCUS_ID 
                    WHERE ac.APCUS_ID = ac1.APCUS_ID ORDER BY aq.APQ_ID DESC) AS APQ_ID FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER ac1";
            $stmt   = $this->db->query($sql);
        } else {
            $sql    = "SELECT * FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_CREATE_BY = ? AND APCUS_STATUS = ?";
            $stmt   = $this->db->query($sql, array($empId, 0));
        }
        
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการลูกค้า",
                    "data"      => $stmt->result_array()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลลูกค้า",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function index_post() {
        $empId  = $this->input->post('empId');
        if ($empId == "") {
            $sql    = "SELECT *, (SELECT TOP 1 aq.APQ_ID FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER ac 
                    LEFT JOIN SQLUAT.TSR_DB1.dbo.ALPINE_QUOTATION aq ON ac.APCUS_ID = aq.APCUS_ID 
                    WHERE ac.APCUS_ID = ac1.APCUS_ID ORDER BY aq.APQ_ID DESC) AS APQ_ID FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER ac1";
            $stmt   = $this->db->query($sql);
        } else {
            $sql    = "SELECT * FROM SQLUAT.TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_CREATE_BY = ? AND APCUS_STATUS = ?";
            $stmt   = $this->db->query($sql, array($empId, 0));
        }
        
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการลูกค้า",
                    "data"      => $stmt->result_array()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลลูกค้า",
                    "data"      => ''
                ), 200
            );
        }
    }
}
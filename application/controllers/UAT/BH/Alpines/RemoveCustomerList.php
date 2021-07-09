<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class RemoveCustomerList extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
        $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        
    }

    public function index_post() {
        $empId          = $this->input->post('empId');
        $customerId     = $this->input->post('customerId');
        $data = array(
            'APCUS_STATUS'      => 2,
            'APCUS_UPDATE_DATE' => date('Y-m-d H:i:s'),
            'APCUS_UPDATE_BY'   => $empId
        );

        $this->db->where('APCUS_ID', $customerId);
        if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "ลบรายการลูกค้า",
                    "data"      => ''
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถลบข้อมูลลูกค้า",
                    "data"      => ''
                ), 200
            );
        }
    }
}
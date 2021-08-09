<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class UpdateQuotationStatus extends REST_Controller{ 
    public function __construct()
    {
        parent::__construct();
         //$this->load->database();
        $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        echo 'AddCustomer';
    }

    public function index_post() {
        $this->db->trans_start();
        $emp_id                     = $this->input->post('empid');
        $quotationId                = $this->input->post('quotationId');
        $comment                    = $this->input->post('comment');
        $status                     = $this->input->post('status');
        $create_date                = date('Y-m-d H:i:s');

        if ($status == "2" || $status == 2) {
            $time = strtotime($comment);
            $newformat = date('Y-m-d',$time);

            $data = array(
                'APQ_EMP_APPROVE'   => $emp_id,
                'APQ_DATE_APPROVE'  => date('Y-m-d'),
                'APQ_STATUS'        => (int)$status,
                'APQ_UPDATE_DATE'   => $create_date,
                'APQ_UPDATE_BY'     => $emp_id,
                'APQ_INSTALL_DATE'  => $newformat,
            );
        } else {
            $data = array(
                'APQ_EMP_APPROVE'   => $emp_id,
                'APQ_DATE_APPROVE'  => date('Y-m-d'),
                'APQ_STATUS'        => (int)$status,
                'APQ_UPDATE_DATE'   => $create_date,
                'APQ_UPDATE_BY'     => $emp_id,
                'APQ_COMMENT'       => $comment,
            );
        }

        $this->db->where('APQ_ID', $quotationId);
        if ($this->db->update('TSR_DB1.dbo.ALPINE_QUOTATION', $data)) {
            $this->db->trans_commit();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "ปรับปรุงข้อมูลสำเร็จ",
                    "data"      => ''
                ), 200
            );
        } else {
            $this->db->trans_rollback();
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                    "data"      => ""
                ), 200
            );
        }
    }
}
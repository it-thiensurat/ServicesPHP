<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetQuotationWaitForApprove extends REST_Controller
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
        $empId  = $this->input->post('empId');
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION WHERE APQ_EMP_ID = ? AND APQ_STATUS IN (0, 3, 4) ORDER BY APQ_ID DESC";
        $stmt   = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            $data = [];
            $result = $stmt->result_array();
            foreach($result as $k => $v) {
                $res = array(
                    'APQ_ID'            => $v['APQ_ID'],
                    'APCUS_ID'          => $v['APCUS_ID'],
                    'APQ_DATE'          => $v['APQ_DATE'],
                    'APQ_DISCOUNT'      => $v['APQ_DISCOUNT'],
                    'APQ_EXPIRE_DATE'   => $v['APQ_EXPIRE_DATE'],
                    'APQ_PROJECTNAME'   => $v['APQ_PROJECTNAME'] . "",
                    'APQ_STATUS'        => $v['APQ_STATUS'],
                    'APQ_COMMENT'       => $v['APQ_COMMENT'] . "",
                    'APQ_STATUS_TEXT'   => $v['APQ_STATUS'] == 0 ? 'รอการอนุมัติ' : $v['APQ_STATUS'] == 3 ? 'แก้ไขใบเสนอราคา' : 'ไม่อนุมัติ',
                    'CUSTOMER_DETAIL'   => $this->getCustomerDetail($v['APCUS_ID']),
                    'PRODUCT_DETAIL'    => $this->getProductDetail($v['APQ_ID'])
                );

                array_push($data, $res);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการใบเสนอราคา",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลใบเสนอราคา",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getCustomerDetail($customer_id) {
        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
        $stmt = $this->db->query($sql, array($customer_id));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getProductDetail($quotationId) {
        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION_DETAILS WHERE APQ_ID = ? AND APQD_STATUS = ?";
        $stmt = $this->db->query($sql, array($quotationId, 1));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }
}
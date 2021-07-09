<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AddProductToQuotation extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
         //$this->load->database();
        $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        echo $this->generateQuotationNumber();
    }

    public function index_post() {

        $this->db->trans_start();
        $emp_id                     = $this->input->post('empId');
        $product                    = $this->input->post('product');
        $customer_id                = $this->input->post('customerId');
        $project_name               = $this->input->post('projectName');
        $promotion_text             = $this->input->post('promotionText');
        $discount                   = $this->input->post('discount');
        $total                      = $this->input->post('total');
        $grand_total                = $this->input->post('grandTotal');
        $date                       = date('Y-m-d H:i:s');
        $currentdate                = date('Y-m-d');

        $product = json_decode($product, true);

        $dataCustomer = array(
            'APCUS_STATUS'      => 1,
            'APCUS_UPDATE_DATE' => $date,
            'APCUS_UPDATE_BY'   => $emp_id
        );

        $this->db->where('APCUS_ID', $customer_id);
        if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER', $dataCustomer)) {
            $quotationId = $this->generateQuotationNumber();
            $dataQuotation = array(
                'APQ_ID'                => $quotationId,
                'APCUS_ID'              => $customer_id,
                'APQ_DATE'              => date('Y-m-d'),
                'APQ_EXPIRE_DATE'       => date("Y-m-d"),
                'APQ_EMP_ID'            => $emp_id,
                'APQ_EMP_NAME'          => $this->getEmpName($emp_id),
                'APQ_PROJECTNAME'       => $project_name,
                'APQ_PROMOTION_TEXT'    => $promotion_text,
                'APQ_DISCOUNT'          => floatval($discount),
                'APQ_TOTAL'             => floatval($total),
                'APQ_GRAND_TOTAL'       => floatval($grand_total),
                'APQ_STATUS'            => 0,
                'APQ_CREATE_BY'         => $emp_id
            );

            if ($this->db->insert('TSR_DB1.dbo.ALPINE_QUOTATION', $dataQuotation)) {
                foreach($product as $k => $v) {
                    $dataDetail = array(
                        'APQ_ID'            => $quotationId,
                        'APQD_PROD_ID'      => $v['product_id'],
                        'APQD_PROD_QTY'     => $v['product_qty'],
                        'APQD_UNIT_PRICE'   => floatval($v['product_price']),
                        'APQD_STATUS'       => 1,
                        'APQD_CREATE_BY'    => $emp_id
                    );
                    $this->db->insert('TSR_DB1.dbo.ALPINE_QUOTATION_DETAILS', $dataDetail);
                }
                $this->db->trans_commit();
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกข้อมูลสำเร็จ",
                        "data"      => $quotationId
                    ), 200
                );
            } else {
                $this->db->trans_rollback();
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถสร้างใบเสนอราคาได้",
                        "data"      => ""
                    ), 200
                );
            }
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

    public function generateQuotationNumber() {
        $sqlCheck   = "SELECT CONCAT(RIGHT(year(GETDATE()), 2), RIGHT('0' + RTRIM(MONTH(GETDATE())), 2), RIGHT('0000' + CONVERT(VARCHAR, CONVERT(INTEGER, SUBSTRING(MAX(APQ_ID), 5, 5) + 1)), 5)) AS APQ_ID  
                        FROM TSR_DB1.dbo.ALPINE_QUOTATION";
        $stmtCheck  = $this->db->query($sqlCheck);
        $running    = $stmtCheck->row()->APQ_ID;
        if ($running != "") {
            return $running;
        } else {
            $sql = "SELECT CONCAT(RIGHT(year(GETDATE()), 2), RIGHT('0' + RTRIM(MONTH(GETDATE())), 2), '00001') AS Running";
            $stmtCheck  = $this->db->query($sql);
            $running    = $stmtCheck->row()->Running;
            return $running;
        }
    }

    public function getEmpName($empId) {
        $sql        = "SELECT prename, namethai, surname FROM TSR_Application.dbo.TSR_Full_EmployeeLogic WHERE empid = ? AND status =1";
        $stmt       = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            $fullname = $stmt->row()->prename . $stmt->row()->namethai . " " . $stmt->row()->surname;
            return $fullname;
        } else {
            return "";
        }
    }
}
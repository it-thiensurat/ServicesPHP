<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class CheckPaymentStatus extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {

    }

    public function index_post() {
        // $refno      = $this->input->post('refno');
        // $contractno = $this->input->post('contractno');
        // $customerid = $this->input->post('customerid');

        $order_id = $this->input->post('order_id');

        // $sql = "SELECT * FROM SQLUAT.TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] 
        //         WHERE PAYMENT_CUSTOMER_ID = ? AND PAYMENT_REFNO = ? AND PAYMENT_CONTRACT_NO = ? AND CONVERT(VARCHAR(10), PAYMENT_CREATE_DATE,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
        // $stmt = $this->db->query($sql, array($customerid, $refno, $contractno));
        $sql = "SELECT A.*, CONCAT(C.Title, C.Firstname) AS CUSTOMER_NAME, C.Lastname AS CUSTOMER_LASTNAME FROM SQLUAT.TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] AS A 
                LEFT JOIN SQLUAT.TSR_DB1.dbo.CUSTOMER_INFO AS C ON A.PAYMENT_CUSTOMER_ID = C.CustomerId
                WHERE A.PAYMENT_ID = ?";
        $stmt = $this->db->query($sql, array($order_id));
        if ($stmt->num_rows() > 0) {
            if ($stmt->row()->PAYMENT_STATUS == 1) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "ชำระค่างวดสำเร็จ",
                        "data"      => $stmt->result_array()
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่มีข้อมูลการจ่ายเงิน",
                        "data"      => $stmt->row()->PAYMENT_STATUS
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลการจ่ายเงิน",
                    "data"      => $stmt->row()->PAYMENT_STATUS
                ), 200
            );
        }
    }

}
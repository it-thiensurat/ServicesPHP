<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class VerifyOtp extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
		$this->load->database();
    }
    
    function index_post() {
        $otp    = $this->input->post('otp');
        $otpref = $this->input->post('otpref');
        $sql    = "SELECT otp_expire_time FROM [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare_OTP WHERE otp = ? AND otp_ref = ?";
        $stmt   = $this->db->query($sql, array($otp, $otpref));
        if ($stmt->num_rows() > 0) {
            $expire_time = $stmt->row()->otp_expire_time;
            $minutes_to_add = 5;
            $time = new DateTime($expire_time);
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $stamp = $time->format('Y-m-d H:i:s');
            $now = date("Y-m-d H:i:s");
            // echo $stamp . " : " . $now;
            $datetime1 = new DateTime($stamp);
            $datetime2 = new DateTime($now);

            if ($datetime1 > $datetime2) {
                $this->response(
                    array(
                        'status' 	=> 'SUCCESS', 
                        'message' 	=> 'ยืนยัน OTP สำเร็จ', 
                        'data' 	    => ''
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        'status' 	=> 'FAILED',
                        'message' 	=> 'รหัส OTP นี้หมดอายุแล้ว กรุณาขอรหัสใหม่',
                        'data' 	    => ''
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED',
                    'message' 	=> 'พบข้อผิดพลาด',
                    'data' 	    => ''
                ), REST_Controller::HTTP_ERROR
            );
        }
    }
}
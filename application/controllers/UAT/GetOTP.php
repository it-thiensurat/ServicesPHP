<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetOTP extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        if ($this->get('otp')) {
            $this->verifyOTP($this->get('otp'), $this->get('mobile'), $this->get('otpRef'));
        } else {
            $this->requestOTP($this->get('mobile'), $this->get('otpRef'), $this->get('contno'), $this->get('serial'));
        }
    }

    function requestOTP($mobile, $otpRef, $contno, $serial) {
        $minutes_to_add = 5;
        $time = new DateTime();
        $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
        $stamp = $time->format('Y-m-d H:i:s');

        $otp = $this->generateOTP();
        $sql = 'INSERT INTO [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare (customer_contno, customer_product_serial, customer_mobile) VALUES (?, ?, ?)';
        $stmt = $this->db->query($sql, array($contno, $serial, $mobile));
        if ($stmt) {
            $sql_otp = 'INSERT INTO [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare_OTP (otp, otp_ref, otp_expire_time) VALUES (?, ?, ?)';
            $stmt_otp = $this->db->query($sql_otp, array($otp, $otpRef, $stamp));
            if ($stmt_otp) {
                $this->response(
                    array(
                        'status' 	=> 'SUCCESS', 
                        'message' 	=> 'Request otp', 
                        'data' 	    => array(
                            'mobile'    => trim($mobile),
                            'otpRef'    => trim($otpRef),
                            'sendSMS'   => $this->sendOTP($otp, $mobile, $otpRef)
                        )
                    ), REST_Controller::HTTP_OK
                );
            } else {
                $this->response(
                    array(
                        'status' 	=> 'FAILED',
                        'message' 	=> 'พบข้อผิดพลาด',
                        'data' 	    => array()
                    ), REST_Controller::HTTP_ERROR
                );
            }
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED',
                    'message' 	=> 'พบข้อผิดพลาด',
                    'data' 	    => array()
                ), REST_Controller::HTTP_ERROR
            );
        }
    }

    function sendOTP($otp, $mobile, $otpRef) {
        $url = "http://app.thiensurat.co.th/api/send_sms/";
        $body = [
            'telno'     => $mobile,
            'message'   => "รหัส OTP คือ " . $otp . " (Ref: " . $otpRef . ")"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body );
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec ($ch);
        curl_close ($ch);
        return $result;
    }

    function verifyOTP($otp, $mobile, $otpRef) {
        $sql = "SELECT otp_expire_time FROM [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare_OTP WHERE otp = ? AND otp_ref = ?";
        $stmt = $this->db->query($sql, array($otp, $otpRef));
        if ($stmt) {
            $result = $stmt->result_array();
            $expire_time = '';
            foreach($result as $k => $v) {
                $expire_time = $v['otp_expire_time'];
            }

            $minutes_to_add = 5;
            $time = new DateTime($expire_time);
            $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
            $stamp = $time->format('Y-m-d H:i:s');
            $now = date("Y-m-d H:i:s");
            // echo $stamp . " : " . $now;
            $datetime1 = new DateTime($stamp);
            $datetime2 = new DateTime($now);

            if ($datetime1 > $datetime2) {
                $sql_update = "UPDATE [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare SET customer_verified = ?, customer_verify_date = ? WHERE customer_mobile = ?";
                $stmt_update = $this->db->query($sql_update, array(1, $now, $mobile));
                if ($stmt_update) {
                    $this->response(
                        array(
                            'status' 	=> 'SUCCESS', 
                            'message' 	=> 'ยืนยันตัวสำเร็จ', 
                            'data' 	    => $this->getDataVerifired($mobile)
                        ), REST_Controller::HTTP_OK
                    );
                } else {
                    $this->response(
                        array(
                            'status' 	=> 'FAILED', 
                            'message' 	=> 'พบข้อผิดพลาด ไม่สามารถยืนยันตัวตนได้', 
                            'data' 	    => array()
                        ), REST_Controller::HTTP_OK
                    );
                }
            } else {
                $this->response(
                    array(
                        'status' 	=> 'FAILED',
                        'message' 	=> 'รหัส OTP นี้หมดอายุแล้ว กรุณาขอรหัสใหม่',
                        'data' 	    => array()
                    ), REST_Controller::HTTP_OK
                );
            }
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED',
                    'message' 	=> 'พบข้อผิดพลาด',
                    'data' 	    => array()
                ), REST_Controller::HTTP_ERROR
            );
        }
    }

    function generateOTP() {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    function getDataVerifired($ref) {
        $sql = "SELECT RTRIM(DM.Refno) AS Refno, RTRIM(DM.CONTNO) AS CONTNO, RTRIM(DM.ProductSerial) AS ProductSerial, RTRIM(DM.IDCard) AS IDCard, RTRIM(DM.IDCard_Status) AS IDCard_Status, RTRIM(DM.PrefixName) AS PrefixName, RTRIM(DM.CustomerName) AS CustomerName,
        RTRIM(DM.TotalPrice) AS TotalPrice, RTRIM(DM.DiscountPrice) AS DiscountPrice, RTRIM(DM.PaidPrice) AS PaidPrice, RTRIM(DM.Outstanding) AS Outstanding, RTRIM(DM.PayType) AS PayType, RTRIM(DM.AllPeriods) AS AllPeriods,
        RTRIM(DM.PayLastPeriod) AS PayLastPeriod, RTRIM(DM.PayLastStatus) AS PayLastStatus, RTRIM(DM.AgingCumulative) AS AgingCumulative, RTRIM(DM.PayPeriod) AS PayPeriod, RTRIM(DM.EffDate) AS EffDate, RTRIM(DM.ProductName) AS ProductName, RTRIM(DM.ProductModel) AS ProductModel, 
        RTRIM(DM.ProductType) AS ProductType, 'http://toss.thiensurat.co.th/ServicesPHP/pictures/no_image.png' AS ProductImage, ISNULL(CS.customer_verified, 0) AS Verified, DM.AgiNote AS Note
        FROM [TSR_Application].dbo.DebtorAnalyze_Master AS DM
        LEFT JOIN [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare AS CS ON DM.CONTNO = CS.customer_contno OR DM.ProductSerial = CS.customer_product_serial
            WHERE CS.customer_mobile = ". $this->db->escape($ref) ." ";
        $stmt = $this->db->query($sql);
        if ($stmt->result_array()) {
            $result = [];
            foreach($stmt->result_array() as $k => $v) {
                $resultdata = array(
                    'Refno'			    => trim($v['Refno']),
                    'CONTNO'		    => trim($v['CONTNO']),
                    'ProductSerial'		=> trim($v['ProductSerial']),
                    'IDCard_Status'		=> trim($v['IDCard_Status']),
                    'PrefixName'		=> trim($v['PrefixName']),
                    'CustomerName'		=> trim($v['CustomerName']),
                    'TotalPrice'		=> trim($v['TotalPrice']),
                    'DiscountPrice'		=> trim($v['DiscountPrice']),
                    'PaidPrice'		    => trim($v['PaidPrice']),
                    'Outstanding'		=> trim($v['Outstanding']),
                    'PayType'		    => trim($v['PayType']),
                    'AllPeriods'		=> trim($v['AllPeriods']),
                    'PayLastPeriod'		=> trim($v['PayLastPeriod']),
                    'PayLastStatus'		=> trim($v['PayLastStatus']),
                    'AgingCumulative'	=> trim($v['AgingCumulative']),
                    'PayPeriod'		    => trim($v['PayPeriod']),
                    'EffDate'		    => trim($v['EffDate']),
                    'ProductName'		=> trim($v['ProductName']),
                    'ProductModel'		=> trim($v['ProductModel']),
                    'ProductType'		=> trim($v['ProductType']),
                    'StatusNote'		=> trim($v['Note']),
                    'ProductImage'		=> trim($v['ProductImage']),
                    'Verify'            => trim($v['Verified']),
                    'Address'           => $this->getAddress(trim($v['Refno']), trim($v['CONTNO']))
                );
                array_push($result, $resultdata);
            }
            return $result;
        } else {
            return null;
        }
    }

    public function getAddress($refno, $contno) {
        $sql = "SELECT AddressTypeCode, AddressDetail, Province, Amphur, District, Zipcode, TelHome, TelMobile FROM [TSR_Application].dbo.DebtorAnalyze_Address 
                WHERE Refno = ? AND CONTNO = ?";
        $stmt = $this->db->query($sql, array($refno, $contno));
        if ($stmt->result_array()) {
            $result = [];
            foreach($stmt->result_array() as $k => $v) {
                $resultdata = array(
                    'AddressType'       => trim($v['AddressTypeCode']),
                    'AddressDetail'     => trim($v['AddressDetail']),
                    'Province'          => trim($v['Province']),
                    'District'          => trim($v['Amphur']),
                    'SubDistrict'       => trim($v['District']),
                    'Zipcode'           => trim($v['Zipcode']),
                    'Phone'             => trim($v['TelHome']),
                    'Mobile'            => trim($v['TelMobile']),
                );
                array_push($result, $resultdata);
            }
        }

        return $result;
    }

    public function index_post()
    {
    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {
    }
}
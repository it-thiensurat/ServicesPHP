<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Register extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        
    }

    public function index_post() {
        $mobile = $this->input->post('mobile');
        $otpref = $this->input->post('otpref');
        $serial = $this->input->post('serial');

        if ($mobile != "") {
            $sql    = "SELECT CI.CustomerId, CI.CitizenId, CI.BirthDate, CI.Sex, CI.Title, CI.Firstname, CI.Lastname, CI.AddressTitle, 
                    REPLACE(CI.AddressTitle2, '-', '') AS AddressTitle2, REPLACE(CI.AddressTitle3, '-', '') AS AddressTitle3, 
                    REPLACE(CI.AddressTitle4, '-', '') AS AddressTitle4, P.ProvinceName, D.DistrictName, SD.SubDistrictName, CI.AddrZipcode, 
                    REPLACE(CI.TelHome, '-', '') AS TelHome, REPLACE(CI.TelMobile, '-', '') AS TelMobile
                    FROM TSR_DB1.dbo.CUSTOMER_INFO AS CI
                    LEFT JOIN TSR_DB1.dbo.Province AS P ON CI.AddrProvince = P.ProvinceCode
                    LEFT JOIN TSR_DB1.dbo.District AS D ON CI.AddrDistrict = D.DistrictCode
                    LEFT JOIN TSR_DB1.dbo.SubDistrict AS SD ON CI.AddrSubdistrict = SD.SubDistrictCode
                    WHERE REPLACE(CI.TelMobile, '-', '') = ?";
                    // WHERE REPLACE(CI.TelMobile, '-', '') = ? OR REPLACE(CI.TelHome, '-', '') = ?";
            $stmt   = $this->db->query($sql, array($mobile));
            // $stmt   = $this->db->query($sql, array($mobile, $mobile));
            $rows   = $stmt->num_rows();
            if ($rows > 0) {
                if ($rows == 1) {
                    $result = $stmt->result_array();
                    $arr = [];
                    foreach($result as $k => $v) {
                        $r = array(
                            'customerID'        => $v['CustomerId'],
                            'cardID'            => $v['CitizenId'],
                            'birthDate'         => $v['BirthDate'],
                            'gender'            => $v['Sex'],
                            'title'             => $v['Title'],
                            'firstName'         => $v['Firstname'],
                            'lastName'          => $v['Lastname'],
                            'address'           => $v['AddressTitle'] . ' ' . $v['AddressTitle2'] . ' ' . $v['AddressTitle3'] . ' ' . $v['AddressTitle4'] ,
                            'province'          => $v['ProvinceName'],
                            'district'          => $v['DistrictName'],
                            'subDistrict'       => $v['SubDistrictName'],
                            'zipcode'           => $v['AddrZipcode'],
                            'telephone'         => $v['TelHome'],
                            'mobile'            => $v['TelMobile'],
                        );

                        array_push($arr, $r);
                    }

                    $this->smsOTP($mobile, $otpref, $arr);
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถลงทะเบียนได้\nกรุณาติดต่อ Call center 1210",
                            "data"      => ''
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่พบหมายเลขนี้ในระบบ\nกรุณาลงทะเบียนด้วยหมายเลขที่แจ้งไว้ตอนซื้อผลิตภัณฑ์",
                        "data"      => ''
                    ), 200
                );
            }
        } else if ($serial != "") {
            $this->registerWithSerial($serial);
        }
    }

    public function registerWithSerial($serial) {
        $sql    = "SELECT * FROM [TSR_DB1].dbo.V_Contract_Master
                    WHERE SerialNo = ? ORDER BY Effdate DESC";
        $stmt   = $this->db->query($sql, array($serial));
        if ($stmt->num_rows() > 0) {
            $this->getCustomerInfo($stmt->row()->Customerid);
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบเลขผลิตภัณฑ์นี้ในระบบ\nกรุณาลงทะเบียนด้วยหมายเลขผลิตภัณฑ์ของบริษัทฯ",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getCustomerInfo($customerId) {
        $sql    = "SELECT CI.CustomerId, CI.CitizenId, CI.BirthDate, CI.Sex, CI.Title, CI.Firstname, CI.Lastname, CI.AddressTitle, 
                    REPLACE(CI.AddressTitle2, '-', '') AS AddressTitle2, REPLACE(CI.AddressTitle3, '-', '') AS AddressTitle3, 
                    REPLACE(CI.AddressTitle4, '-', '') AS AddressTitle4, P.ProvinceName, D.DistrictName, SD.SubDistrictName, CI.AddrZipcode, 
                    REPLACE(CI.TelHome, '-', '') AS TelHome, REPLACE(CI.TelMobile, '-', '') AS TelMobile
                    FROM TSR_DB1.dbo.CUSTOMER_INFO AS CI
                    LEFT JOIN TSR_DB1.dbo.Province AS P ON CI.AddrProvince = P.ProvinceCode
                    LEFT JOIN TSR_DB1.dbo.District AS D ON CI.AddrDistrict = D.DistrictCode
                    LEFT JOIN TSR_DB1.dbo.SubDistrict AS SD ON CI.AddrSubdistrict = SD.SubDistrictCode
                    WHERE CI.CustomerId = ?";
        $stmt   = $this->db->query($sql, array($customerId));
        $rows   = $stmt->num_rows();
        if ($rows > 0) {
            if ($rows == 1) {
                $result = $stmt->result_array();
                $arr = [];
                foreach($result as $k => $v) {
                    $r = array(
                        'customerID'        => $v['CustomerId'],
                        'cardID'            => $v['CitizenId'],
                        'birthDate'         => $v['BirthDate'],
                        'gender'            => $v['Sex'],
                        'title'             => $v['Title'],
                        'firstName'         => $v['Firstname'],
                        'lastName'          => $v['Lastname'],
                        'address'           => $v['AddressTitle'] . ' ' . $v['AddressTitle2'] . ' ' . $v['AddressTitle3'] . ' ' . $v['AddressTitle4'] ,
                        'province'          => $v['ProvinceName'],
                        'district'          => $v['DistrictName'],
                        'subDistrict'       => $v['SubDistrictName'],
                        'zipcode'           => $v['AddrZipcode'],
                        'telephone'         => $v['TelHome'],
                        'mobile'            => $v['TelMobile'],
                    );

                    array_push($arr, $r);
                }

                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "ลงทะเบียนสำเร็จ",
                        "data"      => $arr
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถลงทะเบียนได้\nกรุณาติดต่อ Call center 1210",
                        "data"      => ''
                    ), 200
                );
            }
        } else {
            return null;
        }
    }


    public function smsOTP($mobile, $otpref, $arr) {
        $minutes_to_add = 5;
        $time           = new DateTime();
        $time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
        $stamp          = $time->format('Y-m-d H:i:s');
        $otp            = $this->generateOTP();

        $sql_otp = 'INSERT INTO [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare_OTP (otp, otp_ref, otp_expire_time) VALUES (?, ?, ?)';
        $stmt_otp = $this->db->query($sql_otp, array($otp, $otpref, $stamp));

        if ($stmt_otp) {
            $this->response(
                array(
                    'status' 	=> 'SUCCESS', 
                    'message' 	=> 'ลงทะเบียนสำเร็จ', 
                    'data' 	    => $arr,
                    'otpRef'    => trim($otpref),
                    'sendSMS'   => $this->sendOTP($otp, $mobile, $otpref)
                ), 200
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED',
                    'message' 	=> 'พบข้อผิดพลาด',
                    'data' 	    => array()
                ), 200
            );
        }
    }

    function generateOTP() {
        return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    function sendOTP($otp, $mobile, $otpRef) {
        // $mobile = '0835435373';
        // $mobile = '0861233153';
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
}
<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Login extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        
    }

    public function index_post() {
        $username       = $this->input->post('username');
        $password       = $this->input->post('password');
        $sql            = "SELECT CustomerId FROM TSR_DB1.dbo.CUSTOMER_SMARTCARE_ACCOUNT WHERE Username = ? AND Password = ?";
        $stmt           = $this->db->query($sql, array($username, md5($password)));
        
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "",
                    "data"      => $this->getUserInfo($stmt->row()->CustomerId)
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบผู้ใช้ " . $username,
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getUserInfo($customerid) {
        $sql    = "SELECT CI.CustomerId, CI.CitizenId, CI.BirthDate, CI.Sex, CI.Title, CI.Firstname, CI.Lastname, CI.AddressTitle, 
                    REPLACE(CI.AddressTitle2, '-', '') AS AddressTitle2, REPLACE(CI.AddressTitle3, '-', '') AS AddressTitle3, 
                    REPLACE(CI.AddressTitle4, '-', '') AS AddressTitle4, P.ProvinceName, D.DistrictName, SD.SubDistrictName, CI.AddrZipcode, 
                    REPLACE(CI.TelHome, '-', '') AS TelHome, REPLACE(CI.TelMobile, '-', '') AS TelMobile
                    FROM TSR_DB1.dbo.CUSTOMER_INFO AS CI
                    LEFT JOIN TSR_DB1.dbo.Province AS P ON CI.AddrProvince = P.ProvinceCode
                    LEFT JOIN TSR_DB1.dbo.District AS D ON CI.AddrDistrict = D.DistrictCode
                    LEFT JOIN TSR_DB1.dbo.SubDistrict AS SD ON CI.AddrSubdistrict = SD.SubDistrictCode
                    WHERE CI.CustomerId = ?";
        $stmt   = $this->db->query($sql, array($customerid));
        if ($stmt->num_rows() > 0) {
            // return $stmt->result_array();
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
            return $arr;
        } else {
            return null;
        }
    }
}
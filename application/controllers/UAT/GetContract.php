<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetContract extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $ref = $this->get('ref');
        if (!$ref) {
            $this->response(
                array(
                    "status" => "FAILED",
                    "message" => "กรุณาระบุหมายเลขผลิตภัณฑ์หรือเลขที่สัญญาของท่าน",
                    "data" => ''
                ), 404
            );
            exit();
        }
        $sql = "SELECT RTRIM(DM.Refno) AS Refno, RTRIM(DM.CONTNO) AS CONTNO, RTRIM(DM.ProductSerial) AS ProductSerial, RTRIM(DM.IDCard) AS IDCard, RTRIM(DM.IDCard_Status) AS IDCard_Status, RTRIM(DM.PrefixName) AS PrefixName, RTRIM(DM.CustomerName) AS CustomerName,
        RTRIM(DM.TotalPrice) AS TotalPrice, RTRIM(DM.DiscountPrice) AS DiscountPrice, RTRIM(DM.PaidPrice) AS PaidPrice, RTRIM(DM.Outstanding) AS Outstanding, RTRIM(DM.PayType) AS PayType, RTRIM(DM.AllPeriods) AS AllPeriods,
        RTRIM(DM.PayLastPeriod) AS PayLastPeriod, RTRIM(DM.PayLastStatus) AS PayLastStatus, RTRIM(DM.AgingCumulative) AS AgingCumulative, RTRIM(DM.PayPeriod) AS PayPeriod, RTRIM(DM.EffDate) AS EffDate, RTRIM(DM.ProductName) AS ProductName, RTRIM(DM.ProductModel) AS ProductModel, 
        RTRIM(DM.ProductType) AS ProductType, 'http://toss.thiensurat.co.th/ServicesPHP/pictures/no_image.png' AS ProductImage, ISNULL(CS.customer_verified, 0) AS Verified, DM.AgiNote AS Note
        FROM [TSR_Application].dbo.DebtorAnalyze_Master AS DM
        LEFT JOIN [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare AS CS ON DM.CONTNO = CS.customer_contno OR DM.ProductSerial = CS.customer_product_serial
            WHERE DM.CONTNO = ". $this->db->escape($ref) ." OR DM.ProductSerial = ". $this->db->escape($ref) ." ";
        $stmt = $this->db->query($sql);
        if ($stmt->result_array()) {
            $result = [];
            foreach($stmt->result_array() as $k => $v) {
                $resultdata = array(
                    'Refno'			    => trim($v['Refno']),
                    'CONTNO'		    => trim($v['CONTNO']),
                    'ProductSerial'		=> trim($v['ProductSerial']),
                    'IDCard'            => trim($v['IDCard']),
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
            $this->response(
                array(
                    "status" => "SUCCESS",
                    "message" => "Contract",
                    "data" => $result
                ), 200
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED'
                    ,'message' 	=> 'ไม่พบข้อมูลในระบบ'
                    ,'data' 	=> array()
                ), 404
            );
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

    public function index_post() {
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
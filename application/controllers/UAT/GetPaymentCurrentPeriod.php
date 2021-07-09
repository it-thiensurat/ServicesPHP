<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetPaymentCurrentPeriod extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $ref = $this->get('contno');
        $sql = "SELECT * FROM OPENQUERY ([TSR-MOBILESYSTEM-PRD], 
                'SELECT C.CONTNO, C.ContractReferenceNo,Pro.ProductName,Pro.ProductPrice,C.Sales,C.totalprice,C.MODE AS Periods,C.ProductSerialNumber
                ,(SELECT TOP 1 PaymentPeriodNumber FROM Bighead_Mobile.dbo.SalePaymentPeriod WHERE PaymentComplete = 0 AND C.RefNo = RefNo ORDER BY PaymentPeriodNumber) AS PaymentPeriodNumber
                ,(SELECT TOP 1 NetAmount FROM Bighead_Mobile.dbo.SalePaymentPeriod WHERE PaymentComplete = 0 AND C.RefNo = RefNo ORDER BY PaymentPeriodNumber) AS NetAmount
                ,DC.PrefixName,DC.CustomerName,DC.IDCard,Addr.addressDetail,Addr.addressDetail2,Addr.addressDetail3,Addr.addressDetail4
                ,(SELECT TOP 1 SubDistrictName From Bighead_Mobile.dbo.SubDistrict WHERE SubDistrictCode = Addr.SubDistrictCode) AS SubDistrictName
                ,(SELECT TOP 1 DistrictName From Bighead_Mobile.dbo.District WHERE DistrictCode = Addr.DistrictCode) AS DistrictName
                ,(SELECT TOP 1 ProvinceName From Bighead_Mobile.dbo.Province WHERE ProvinceCode = Addr.ProvinceCode) AS ProvinceName
                ,Zipcode, TelHome, TelOffice, TelMobile FROM Bighead_Mobile.dbo.Contract AS C
                INNER JOIN TSRData_Source.dbo.vw_DebtorCustomer AS DC ON C.CustomerID = DC.CustomerID
                INNER JOIN Bighead_Mobile.dbo.Address AS Addr ON C.RefNo = Addr.RefNo AND AddressTypeCode = ''AddressInstall''
                INNER JOIN Bighead_Mobile.dbo.Product AS Pro ON C.ProductID = Pro.ProductID 
                WHERE C.status = ''NORMAL'' AND C.isActive = 1 AND C.CONTNO = ''$contno'' ')";
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status" => "SUCCESS",
                "message" => "Contract",
                "data" => $stmt->result_array()
            ), 200
        );
    }

    public function index_post() {
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
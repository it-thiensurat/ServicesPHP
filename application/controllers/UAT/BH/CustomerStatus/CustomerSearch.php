<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class CustomerSearch extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
    //    echo phpinfo();
        $searchValue = $this->input->get('search');
        $sql = "SELECT * FROM 
				(
					SELECT M.Refno, M.CONTNO, M.IDCard,	M.PrefixName, 
					M.CustomerName, ISNULL(M.PayLastStatus, '') AS PayLastStatus, ISNULL(M.CustomerStatus, '') AS CustomerStatus, ISNULL(M.AccountStatus, '') AS AccountStatus, 
					ISNULL(M.PayType, '') AS PayType, ISNULL(M.AllPeriods, '') AS AllPeriods, ISNULL(M.PayLastPeriod, '') AS PayLastPeriod, ISNULL(M.TotalPrice, '') AS TotalPrice, 
					ISNULL(M.ProductName, '') AS ProductName, ISNULL(M.ProductModel, '') AS ProductModel, M.SaleCode, convert(varchar, M.EffDate, 103) AS EffDate,
					ISNULL(M.AgingCumulative, '') AS AgingCumulative, ISNULL(M.AgingContinuous, '') AS AgingContinuous, ISNULL(A.AgingCumulativeDetail, '') AS AgingCumulativeDetail, 
                    ISNULL(convert(varchar, M.StDate, 103), '') AS StDate, 
                    DATEDIFF(year, M.EffDate, GETDATE()) AS df, 
                    ISNULL(AD.TelHome, '') AS TelHome, ISNULL(AD.TelMobile, '') AS TelMobile
					FROM TSR_Application.dbo.DebtorAnalyze_Master AS M
					LEFT JOIN TSR_Application.dbo.DebtorAnalyze_AgingStatus AS A ON M.CustomerStatus = A.AgingCumulative AND M.AccountStatus = A.AgingContinuous
                    LEFT JOIN TSR_Application.dbo.DebtorAnalyze_Address AS AD ON M.Refno = AD.Refno AND AD.AddressTypeCode = 'AddressIDCard'
                    WHERE M.CustomerStatus IN ('T', 'R', 'N')
				) AS MT 
				WHERE MT.IDCard = '" . $searchValue . "' OR MT.CustomerName LIKE '%" . $searchValue . "%' AND MT.df <= 10 ORDER BY MT.EffDate DESC";	
        $stmt = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            // if ($stmt->row()->CustomerStatus == "T" || $stmt->row()->CustomerStatus == "R") {
                $data = [];
                foreach($stmt->result_array() as $k => $v){
                    $d = array(
                        'Refno'                 => $v["Refno"], 
                        'CONTNO'                => $v["CONTNO"],  
                        'IDCard'                => $v["IDCard"], 	
                        'PrefixName'            => $v["PrefixName"],  
                        'CustomerName'          => $v["CustomerName"],  
                        'PayLastStatus'         => $v["PayLastStatus"],  
                        'CustomerStatus'        => $v["CustomerStatus"],  
                        'AccountStatus'         => $v["AccountStatus"],  
                        'PayType'               => $v["PayType"],  
                        'AllPeriods'            => $v["AllPeriods"],  
                        'PayLastPeriod'         => $v["PayLastPeriod"],  
                        'TotalPrice'            => $v["TotalPrice"],  
                        'ProductName'           => $v["ProductName"],  
                        'ProductModel'          => $v["ProductModel"],  
                        'SaleCode'              => $v["SaleCode"],
                        'EffDate'               => $v["EffDate"], 
                        'AgingCumulative'       => $v["AgingCumulative"],  
                        'AgingContinuous'       => $v["AgingContinuous"],  
                        'AgingCumulativeDetail' => $v["AgingCumulativeDetail"],  
                        'StDate'                => $v["StDate"], 
                        'Address'               => $this->getCustomerAddress($v["Refno"])
                    );

                    array_push($data, $d);
                }

                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "ประวัติลูกค้า",
                        "data"      => $data
                    ), 200
                );
            // } else {
            //     $this->response(
            //         array(
            //             "status"    => "FAILED",
            //             "message"   => "ลูกค้าประวัติดี",
            //             "data"      => ""
            //         ), 200
            //     );
            // }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบประวัติของลูกค้า",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function index_post() {
        $searchValue = $this->input->post('search');
        $sql = "SELECT * FROM 
				(
					SELECT M.Refno, M.CONTNO, M.IDCard,	M.PrefixName, 
					M.CustomerName, ISNULL(M.PayLastStatus, '') AS PayLastStatus, ISNULL(M.CustomerStatus, '') AS CustomerStatus, ISNULL(M.AccountStatus, '') AS AccountStatus, 
					ISNULL(M.PayType, '') AS PayType, ISNULL(M.AllPeriods, '') AS AllPeriods, ISNULL(M.PayLastPeriod, '') AS PayLastPeriod, ISNULL(M.TotalPrice, '') AS TotalPrice, 
					ISNULL(M.ProductName, '') AS ProductName, ISNULL(M.ProductModel, '') AS ProductModel, M.SaleCode, convert(varchar, M.EffDate, 103) AS EffDate,
					ISNULL(M.AgingCumulative, '') AS AgingCumulative, ISNULL(M.AgingContinuous, '') AS AgingContinuous, ISNULL(A.AgingCumulativeDetail, '') AS AgingCumulativeDetail, 
                    ISNULL(convert(varchar, M.StDate, 103), '') AS StDate, 
                    DATEDIFF(year, M.EffDate, GETDATE()) AS df, 
                    ISNULL(AD.TelHome, '') AS TelHome, ISNULL(AD.TelMobile, '') AS TelMobile
					FROM TSR_Application.dbo.DebtorAnalyze_Master AS M
					LEFT JOIN TSR_Application.dbo.DebtorAnalyze_AgingStatus AS A ON M.CustomerStatus = A.AgingCumulative AND M.AccountStatus = A.AgingContinuous
                    LEFT JOIN TSR_Application.dbo.DebtorAnalyze_Address AS AD ON M.Refno = AD.Refno AND AD.AddressTypeCode = 'AddressIDCard'
                    WHERE M.CustomerStatus IN ('T', 'R', 'N')
				) AS MT 
				WHERE MT.IDCard = ? OR MT.CustomerName LIKE '" . $searchValue . "%' OR MT.CustomerName LIKE '%" . $searchValue . "' OR REPLACE(MT.TelHome, '-', '') = ? OR REPLACE(MT.TelMobile, '-', '') = ? 
                AND MT.df <= 10
                ORDER BY MT.EffDate DESC";	
        $stmt = $this->db->query($sql, array($searchValue, $searchValue, $searchValue));
        if ($stmt->num_rows() > 0) {
            // if ($stmt->row()->CustomerStatus == "T" || $stmt->row()->CustomerStatus == "R") {
                $data = [];
                foreach($stmt->result_array() as $k => $v){
                    $d = array(
                        'Refno'                 => $v["Refno"], 
                        'CONTNO'                => $v["CONTNO"],  
                        'IDCard'                => $v["IDCard"], 	
                        'PrefixName'            => $v["PrefixName"],  
                        'CustomerName'          => $v["CustomerName"],  
                        'PayLastStatus'         => $v["PayLastStatus"],  
                        'CustomerStatus'        => $v["CustomerStatus"],  
                        'AccountStatus'         => $v["AccountStatus"],  
                        'PayType'               => $v["PayType"],  
                        'AllPeriods'            => $v["AllPeriods"],  
                        'PayLastPeriod'         => $v["PayLastPeriod"],  
                        'TotalPrice'            => $v["TotalPrice"],  
                        'ProductName'           => $v["ProductName"],  
                        'ProductModel'          => $v["ProductModel"],  
                        'SaleCode'              => $v["SaleCode"],
                        'EffDate'               => $v["EffDate"], 
                        'AgingCumulative'       => $v["AgingCumulative"],  
                        'AgingContinuous'       => $v["AgingContinuous"],  
                        'AgingCumulativeDetail' => $v["AgingCumulativeDetail"],  
                        'StDate'                => $v["StDate"], 
                        'Address'               => $this->getCustomerAddress($v["Refno"])
                    );

                    array_push($data, $d);
                }

                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "ประวัติลูกค้า",
                        "data"      => $data
                    ), 200
                );
            // } else {
            //     $this->response(
            //         array(
            //             "status"    => "FAILED",
            //             "message"   => "ลูกค้าประวัติดี",
            //             "data"      => ""
            //         ), 200
            //     );
            // }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบประวัติของลูกค้า",
                    "data"      => ""
                ), 200
            );
        }
    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {
    }

    public function getCustomerAddress($refno) {
        $sql = "SELECT ISNULL(AddressDetail, '') AS AddressDetail, ISNULL(AddressDetail2, '') AS AddressDetail2, ISNULL(AddressDetail3, '') AS AddressDetail3, 
                ISNULL(AddressDetail4, '') AS AddressDetail4, Province, Amphur, District, Zipcode, ISNULL(Latitude, '') AS Latitude, ISNULL(Longitude, '') AS Longitude,
                ISNULL(TelHome, '') AS TelHome, ISNULL(TelMobile, '') AS TelMobile, ISNULL(TelOffice, '') AS TelOffice, ISNULL(EMail, '') AS EMail
                FROM TSR_Application.dbo.DebtorAnalyze_Address 
                WHERE Refno = ? AND AddressTypeCode = 'AddressInstall'";
        $stmt = $this->db->query($sql, array($refno));
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $d = array(
                    'AddressDetail'     => $v["AddressDetail"], 
                    'AddressDetail2'    => $v["AddressDetail2"],  
                    'AddressDetail3'    => $v["AddressDetail3"],  
                    'AddressDetail4'    => $v["AddressDetail4"],  
                    'Province'          => $v["Province"],  
                    'Amphur'            => $v["Amphur"],  
                    'District'          => $v["District"],  
                    'Zipcode'           => $v["Zipcode"],  
                    'Latitude'          => $v["Latitude"],  
                    'Longitude'         => $v["Longitude"], 
                    'TelHome'           => $v["TelHome"],  
                    'TelMobile'         => $v["TelMobile"],  
                    'TelOffice'         => $v["TelOffice"],  
                    'EMail'             => $v["EMail"],
                );

                array_push($data, $d);
            }
            return $data;
        } else {
            return [];
        }
    }
}
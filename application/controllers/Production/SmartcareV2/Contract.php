<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Contract extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        // $contract = '70147113';
        // $contract2 = '31501161';
        $sql = "SELECT * FROM [TSR_DB1].dbo.V_Contract_Master
                WHERE ContractNo IN (?, ?) ORDER BY Effdate DESC";
        $stmt = $this->db->query($sql, array($contract, $contract2));
        if ($stmt->num_rows() > 0) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "testapi",
                    "data"      => $stmt->result_array()
                )
                , 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "testapi",
                    "data"      => ''
                )
                , 200
            );
        }
    }

    public function index_post() {
        $customerID = $this->input->post('customer_id');
        // $customerID = '156117';
        $sql = "SELECT * FROM [TSR_DB1].dbo.V_Contract_Master
                WHERE CustomerId = ? ORDER BY Effdate DESC";
                
        $stmt = $this->db->query($sql, array($customerID));
        if ($stmt->num_rows() > 0) {
            $arr = [];
            $result = $stmt->result_array();
            foreach($result as $k => $v) {
                $r = array(
                    "ContractId"            => $v['ContractId'],               
                    "RefNo"                 => $v['RefNo'],               
                    "CompCode"              => $v['CompCode'],               
                    "CustomerName"          => $v['CustomerName'],
                    "CustomerCardId"        => $v['CustomerCardId'],           
                    "ContractNo"            => $v['ContractNo'],               
                    "Effdate"               => $v['Effdate'],               
                    "ContractStatus"        => $v['ContractStatus'],               
                    "SerialNo"              => $v['SerialNo'],               
                    "ContractPeriod"        => $v['ContractPeriod'],               
                    "Sales"                 => $v['Sales'],               
                    "Credit"                => $v['Credit'],               
                    "ContractPeriodAmount"  => $v['ContractPeriodAmount'],               
                    "Customerid"            => $v['Customerid'],               
                    "PayLastPeriod"         => $v['PayLastPeriod'],               
                    "PayNextPeriod"         => $v['PayNextPeriod'],               
                    "NextDueDate"           => $v['NextDueDate'],               
                    "PackageId"             => $v['PackageId'],               
                    "ItemCode"              => $v['ItemCode'],               
                    "kinddesc"              => $v['kinddesc'],               
                    "kindname"              => $v['kindname'],               
                    "Des"                   => $v['Des'],
                    "ContractStatusName"    => $v['ContractStatusName'],
                    "ProductImage"          => $this->getProductImage($v['ItemCode'], $v['kinddesc']),
                    "payment"               => $this->getPaymentTransaction($v['ContractNo']),
                    "paymentDetail"         => $this->getPaymentDetail($v['ContractNo'])
                );

                array_push($arr, $r);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get Contract",
                    "data"      => $arr
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถดึงข้อมูลสัญญาได้",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getPaymentTransaction($contno) {
        $url    = "https://tssm.thiensurat.co.th/api/customerreceiptapi.php?contno=" . $contno;
        $json   = file_get_contents($url);
        $obj    = json_decode($json, true);
        return $obj;
    }

    public function getPaymentDetail($contno) {
        $url    = "https://tssm.thiensurat.co.th/api/api-checkSmartCareRecreipt.php?contno=" . $contno;
        $json   = file_get_contents($url);
        $obj    = json_decode($json, true);
        return $obj;
    }

    public function getProductImage($model, $type) {
        $sql = "SELECT ProductImagePath FROM TSR_Application.dbo.DebtorAnalyze_Images WHERE ProductModel = ?";
        $stmt = $this->db->query($sql, array($model));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->ProductImagePath;
        } else {
            if ($type == "เครื่องกรองน้ำ") {
                return 'http://app.thiensurat.co.th/productimages/machine.jpg';
            } else if ($type == "ชุดสารกรอง") {
                return 'http://app.thiensurat.co.th/productimages/filer.jpg';
            } else {
                return 'http://app.thiensurat.co.th/productimages/no_image.png';
            }
        }
    }
}
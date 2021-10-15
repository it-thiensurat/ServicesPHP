<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetImageCheck extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        $this->baseUrl = "http://bof.thiensurat.co.th/mca/images/NewcontractImage/";
    }

    public function index_post() {
        $empid      = $this->input->post('empid');
        // $empid      = "A33749";
        $sql        = "SELECT ac.RefNo, c.CONTNO, c.EFFDATE, p.ProductName, p.ProductModel, c.ProductSerialNumber, ac.comment, ac.createby, ac.Approve,
                        d.PrefixName, d.CustomerName
                        FROM TSR_Application.dbo.ApproveContno ac
                        LEFT JOIN Bighead_Mobile.dbo.Contract c ON ac.RefNo = c.RefNo
                        LEFT JOIN Bighead_Mobile.dbo.Product p ON c.ProductID = p.ProductID
                        LEFT JOIN Bighead_Mobile.dbo.DebtorCustomer d ON c.CustomerID = d.CustomerID
                        WHERE ac.createby = ? AND ac.status = 1 and ac.Approve IN (2, 3) AND CONVERT(VARCHAR(10), ac.createdate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
        $stmt       = $this->db->query($sql, array($empid));
        if ($stmt->num_rows() > 0) {
            $data = [];
            $result = $stmt->result_array();
            foreach($result as $k => $v) {
                $d = array(
                    'Refno'                 => $v["RefNo"],
                    'CONTNO'                => $v["CONTNO"],
                    'EFFDATE'               => $v["EFFDATE"],
                    'ProductName'           => $v["ProductName"],
                    'ProductModel'          => $v["ProductModel"],
                    'ProductSerialNumber'   => $v["ProductSerialNumber"],
                    'comment'               => $v["comment"],
                    'createby'              => $v["createby"],
                    'PrefixName'            => $v["PrefixName"],
                    'FirstName'             => $v["CustomerName"],
                    'LastName'              => "",
                    'ApproveStatus'         => $v["Approve"],
                    'image'                 => $this->getImageDetail($v["RefNo"]),
                    'yellowFlag'            => $this->getYellowFlag($v["RefNo"])
                );

                array_push($data, $d);
            }

            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการตรวจสอบ",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลการตรวจสอบ",
                    "data"      => []
                ), 200
            );
        }
    }

    public function getImageDetail($refno) {
        // $sql = "SELECT ai.id, ai.RefNo, ai.createby, ai.ImageTypeCode, CONCAT('http://bof.thiensurat.co.th/mca/images/NewcontractImage/', ai.ImageName) AS ImageName, 
        //         ai.ProblemId, ap.ProblemNameV3 
        //         FROM TSR_Application.dbo.ApproveImageType AS ai
        //         LEFT JOIN TSR_ONLINE_MARKETING.dbo.v_AllProblem AS ap ON ai.ProblemId = ap.ProblemIDV3
        //         WHERE ai.RefNo = ? AND ai.status = 1 and ai.pass = 0";
        $sql = "SELECT ai.id, ai.RefNo, ai.createby, ai.ImageTypeCode, CONCAT('http://bof.thiensurat.co.th/mca/images/NewcontractImage/', ai.ImageName) AS ImageName, 
                ai.ProblemId, ap.ProblemName 
                FROM TSR_Application.dbo.ApproveImageType AS ai
                LEFT JOIN TSR_Application.dbo.ApproveContnoProblem AS ap ON ai.ProblemId = ap.ProblemId
                WHERE ai.RefNo = ? AND ai.status = 1 and ai.pass = 0";
        $stmt = $this->db->query($sql, array($refno));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }
    
    public function getYellowFlag($refno) {
        $sql = "SELECT CASE WHEN (SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'mappayment' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [1]
                ,CASE WHEN (SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'MAP' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [2]
                ,CASE WHEN (SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'PAYMENTCARD' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [3]
                ,CASE WHEN LEN(a.TelOffice) > 1 AND LEN(a.TelMobile) > 1 THEN 0 ELSE 1 END [4]
                ,CASE WHEN ( SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'PRODUCT' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [5]
                ,CASE WHEN ( SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'IdCard' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [6]
                ,CASE WHEN ( SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'ADDRESS' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [7]
                ,CASE WHEN ( SELECT top 1 _a.problemid FROM TSR_Application.[dbo].[ApproveImageType] _a WHERE _a.status = 1 AND  _a.imagetypecode = 'CUSTOMER' AND  _a.refno = c.RefNo AND _a.YellowFlag = 1) IS NOT NULL THEN 1 ELSE 0 END AS [8]
                ,CASE WHEN ci.customerType = 0 AND ci.AuthorizedIDCard IS NOT NULL AND PATINDEX('%#%',ci.AuthorizedIDCard) > 0 THEN  0 ELSE 1 END [9]
                FROM Bighead_Mobile.dbo.Contract c 
                LEFT JOIN [Bighead_Mobile].[dbo].[Address] a ON a.RefNo = c.RefNo AND a.AddressTypeCode = 'AddressInstall'
                LEFT JOIN Bighead_Mobile.dbo.DebtorCustomer ci ON ci.CustomerID = c.CustomerID
                WHERE c.RefNo = ?";
        $stmt = $this->db->query($sql, array($refno));
        return $stmt->result_array();
    }
}
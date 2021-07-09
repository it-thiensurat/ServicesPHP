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
                    'image'                 => $this->getImageDetail($v["RefNo"])
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
        $sql = "SELECT ai.id, ai.RefNo, ai.createby, ai.ImageTypeCode, CONCAT('http://bof.thiensurat.co.th/mca/images/NewcontractImage/', ai.ImageName) AS ImageName, 
                ai.ProblemId, ap.ProblemNameV3 
                FROM TSR_Application.dbo.ApproveImageType AS ai
                LEFT JOIN TSR_ONLINE_MARKETING.dbo.v_AllProblem AS ap ON ai.ProblemId = ap.ProblemIDV3
                WHERE ai.RefNo = ? AND ai.status = 1 and ai.pass = 0";
        $stmt = $this->db->query($sql, array($refno));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }   
}
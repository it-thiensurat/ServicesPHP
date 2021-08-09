<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetPay100 extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $this->getPay();
    }

    public function index_post() {
        $teamcode   = $this->input->post('teamcode');
        $depid      = $this->input->post('depid');
        $fnyear     = $this->input->post('fnyear');
        $fnno       = $this->input->post('fnno');
        if ($this->getPay() == 1) {
            $sql = "SELECT TeamID, TeamCode, FnYear, FnNo, DepID, SupCheckTime, SupCheckNum, SupApproveNum, SupNotApproveNum, EmpID
                    FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100
                    WHERE TeamCode = ? AND DepID = ? AND FnYear = ? AND FnNo = ?
                    AND SupApproveStatus = 1 AND CONVERT(VARCHAR(10), SupCheckTime, 126) = CONVERT(VARCHAR(10), GETDATE(), 126)";
            $stmt = $this->db->query($sql, array($teamcode, $depid, $fnyear, $fnno));
            if ($stmt->num_rows() > 0) {
                $result = $stmt->result_array();
                $data = [];
                foreach($result as $k => $v) {
                    $r = array(
                        'TeamID'            => $v["TeamID"],
                        'TeamCode'          => $v["TeamCode"],
                        'FnYear'            => $v["FnYear"],
                        'FnNo'              => $v["FnNo"],
                        'DepID'             => $v["DepID"],
                        'SupCheckTime'      => $v["SupCheckTime"],
                        'SupCheckNum'       => $v["SupCheckNum"],
                        'SupApproveNum'     => $v["SupApproveNum"],
                        'SupNotApproveNum'  => $v["SupNotApproveNum"],
                        'EmpID'             => $v["EmpID"],
                        'WorkDetail'        => $this->getWorkDetail($v["TeamID"])
                    );

                    array_push($data, $r);
                }
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "รายการอนุมัติจ่ายเงิน",
                        "data"      => $data
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่มีรายการอนุมัติจ่ายเงิน",
                        "data"      => ""
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ยังไม่ถึงกำหนดการจ่าย",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function getWorkDetail($teamid) {
        $sql = "SELECT stwd.*, stwp.PaymentImage FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail stwd
                LEFT JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Payment stwp ON stwd.DetailID = stwp.DetailID
                WHERE stwd.TeamID = ? AND stwd.SupApproveStatus = 1";
        $stmt = $this->db->query($sql, array($teamid));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getPay() {
        $date = date('d-m-Y');
        $sql = "SELECT CONVERT(varchar, LockDate, 105), IsActive FROM SQLUAT.TSR_Application.dbo.CostBranch_LockData_100
                WHERE CONVERT(varchar, LockDate, 105) = ? AND IsActive = 1";
        $stmt = $this->db->query($sql, array($date));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }
}

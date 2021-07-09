<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetTeamCheck extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $teamcode   = $this->input->post('teamcode');
        $depid      = $this->input->post('depid');
        $fnyear     = $this->input->post('fnyear');
        $fnno       = $this->input->post('fnno');
        $sql = "SELECT TeamID,TeamCode,FnYear,FnNo,DepID,LeadCheckTime,LeadCheckNum,LeadCheckWorkNum,LeadCheckOutNum,EmpID
                FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work
                WHERE TeamCode = ? AND DepID = ? AND FnYear = ? AND FnNo = ?
                AND LeadApproveStatus = 1 AND CONVERT(VARCHAR(10),LeadCheckTime,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
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
                    'LeadCheckTime'     => $v["LeadCheckTime"],
                    'LeadCheckNum'      => $v["LeadCheckNum"],
                    'LeadCheckWorkNum'  => $v["LeadCheckWorkNum"],
                    'LeadCheckOutNum'   => $v["LeadCheckOutNum"],
                    'EmpID'             => $v["EmpID"],
                    'CostBranch'        => $this->getCostBranch(),
                    'WorkDetail'        => $this->getWorkDetail($v["TeamID"])
                );

                array_push($data, $r);
            }

            $this->response(
                array(
                    "status"                => "SUCCESS",
                    "message"               => "รายการลงเวลา",
                    "SupApproveStatus"      => $this->getApproveStatus($teamcode, $depid, $fnyear, $fnno),
                    "data"                  => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลการลงเวลา",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function getWorkDetail($teamid) {
        $sql = "SELECT DetailID, TeamID, EmpID, EmpName, SaleCode, LeadCheckTime,
                CitizenID, CONVERT(varchar(10), PaymentAmount) AS PayAmount,
                -- CASE
                -- 	WHEN SupCheckTime IS NULL THEN LeadApproveStatus
                -- 	ELSE SupApproveStatus
                -- END  AS LeadApproveStatus,
                LeadApproveStatus,
                Image,
                REPLACE(EmpID,'A','0') AS EmpImage,
                CASE
                	WHEN SupCheckTime IS NULL THEN LeadApproveStatus
                	ELSE SupApproveStatus
                END  AS SwitchStatus
                FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail
                WHERE TeamID = ?";
        $stmt = $this->db->query($sql, array($teamid));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return null;
        }
    }

    public function getCostBranch() {
        $date = date('d-m-Y');
        $sql = "SELECT CONVERT(varchar, LockDate, 105), IsActive FROM SQLUAT.Allowance.dbo.CostBranch_LockData
                WHERE CONVERT(varchar, LockDate, 105) = ? AND IsActive = 1";
        $stmt = $this->db->query($sql, array($date));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getApproveStatus($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT SupApproveStatus FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work
                WHERE TeamCode = ? AND DepID = ? AND FnYear = ? AND FnNo = ?
                AND CONVERT(VARCHAR(10),LeadCheckTime,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->SupApproveStatus;
        } else {
            return 0;
        }
    }
}

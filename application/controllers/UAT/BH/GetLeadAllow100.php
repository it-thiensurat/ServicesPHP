<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetLeadAllow100 extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $teamno     = $this->input->post('teamcode');
        $depid      = $this->input->post('depid');
        $fnyear     = $this->input->post('fnyear');
        $fnno       = $this->input->post('fnno');

        $sql = "SELECT * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100
                WHERE TeamCode = ? AND DepID = ? AND FnYear = ? AND FnNo = ?
                AND LeadApproveStatus = 1 AND CONVERT(VARCHAR(10),LeadCheckTime,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnyear, $fnno));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $data = [];
            foreach($result as $k => $v) {
                $r = array(
                    'TeamID'                => $v["TeamID"],
                    'TeamCode'              => $v["TeamCode"],
                    'FnYear'                => $v["FnYear"],
                    'FnNo'                  => $v["FnNo"],
                    'DepID'                 => $v["DepID"],
                    'LeadCheckTime'         => $v["LeadCheckTime"],
                    'LeadCheckNum'          => $v["LeadCheckNum"],
                    'LeadApproveNum'        => $v["LeadApproveNum"],
                    'LeadNotApproveNum'     => $v["LeadNotApproveNum"],
                    'EmpID'                 => $v["EmpID"],
                    'WorkDetail'            => $this->getWorkDetail($v["TeamID"], $v["EmpID"])
                );

                array_push($data, $r);
            }

            $this->response(
                array(
                    "status"            => "SUCCESS",
                    "message"           => "รายการยืนยันการจ่ายเฝิน",
                    "data"              => $data,
                    'CostBranch'        => $this->getPay(),
                    "SupApproveStatus"  => $this->getApproveStatus($teamno, $depid, $fnyear, $fnno),
                    "cause"             => $this->getReason()
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลการจ่ายเงิน",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function getWorkDetail($teamid) {
        $sql = "SELECT swd.DetailID, swd.TeamID, swd.EmpID, swd.EmpName, swd.SaleCode, swd.LeadCheckTime, swd.SupCheckTime, 
                swd.CitizenID, swd.FnYear, swd.FnNo, swd.LeadApproveStatus, swd.LeadCause, swd.SupCause,
                CASE 
                	WHEN swd.SupCheckTime IS NULL THEN swd.LeadApproveStatus
                	ELSE swd.SupApproveStatus 
                END AS SwitchStatus,
                cc.causeName, swd.PaymentAmount, swd.PaymentStatus, swd.PaymentBalance, swd.SupApproveStatus 
                FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail AS swd
                LEFT JOIN TSR_Application.dbo.CostBranch_CauseMaster AS cc ON swd.LeadCause = cc.id
                WHERE swd.TeamID = ?";
        $stmt = $this->db->query($sql, array($teamid));
        if ($stmt) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $d = array(
                    'DetailID'          => $v['DetailID'], 
                    'TeamID'            => $v['TeamID'], 
                    'EmpID'             => $v['EmpID'], 
                    'EmpName'           => $v['EmpName'], 
                    'SaleCode'          => $v['SaleCode'], 
                    'LeadCheckTime'     => $v['LeadCheckTime'], 
                    'CitizenID'         => $v['CitizenID'],
                    'FnYear'            => $v['FnYear'],
                    'FnNo'              => $v['FnNo'],
                    'PaymentAmount'     => $v['PaymentAmount'],
                    'PaymentBalance'    => $v['PaymentBalance'],
                    'PaymentStatus'     => $v['PaymentStatus'],
                    'LeadApproveStatus' => $v['LeadApproveStatus'],
                    'LeadCauseName'     => is_null($v['causeName']) ? "" : $v['causeName'],
                    'LeadCause'         => $v['LeadCause'],
                    'SwitchStatus'      => is_null($v["SupCheckTime"]) ? $v['LeadApproveStatus'] : $v["SupApproveStatus"],
                    'CauseStatus'       => is_null($v["SupCheckTime"]) ? $v['LeadCause'] : $v["SupCause"]
                );

                array_push($data, $d);
            }

            return $data;
        } else {
            return null;
        }
    }

    public function getReason() {
        $sql = "SELECT id, causeName FROM TSR_Application.[dbo].[CostBranch_CauseMaster]";
        $stmt = $this->db->query($sql);
        if ($stmt) {
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

    public function getApproveStatus($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT SupApproveStatus FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100 WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ? 
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->SupApproveStatus;
        } else {
            return 0;
        }
    }
}
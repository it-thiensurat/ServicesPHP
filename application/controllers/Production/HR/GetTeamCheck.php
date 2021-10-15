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
        $fnno       = $this->getFnNo($depid);
        $fnyear     = $this->getFnYear($depid);
        $costbranch = $this->getCostBranch();
        $supapprove = $this->getApproveStatus($teamcode, $depid, $fnyear, $fnno);
        $sql = "SELECT TeamID,TeamCode,FnYear,FnNo,DepID,LeadCheckTime,LeadCheckNum,LeadCheckWorkNum,LeadCheckOutNum,EmpID
                FROM TSR_DB1.dbo.SaleTeam_Work
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
                    'CostBranch'        => $costbranch,
                    'WorkDetail'        => $costbranch == 0 ? $this->getWorkDetail($v["FnNo"], $v["FnYear"], $v["DepID"], $v["TeamID"]) : $this->getWorkDetail_lockCostBranch($v["FnNo"], $v["FnYear"], $v["DepID"], $v["TeamID"])
                );

                array_push($data, $r);
            }

            $this->response(
                array(
                    "status"                => "SUCCESS",
                    "message"               => "รายการลงเวลา",
                    "SupApproveStatus"      => $supapprove,
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

    public function getFnNo($depid) {
        $sql = "SELECT TOP 1 Fortnight_no FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ?
                AND (CAST(DATEADD(YEAR, 543, GETDATE()) AS DATE) BETWEEN OpenDate AND CloseDate) ORDER BY Fortnight_no DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->Fortnight_no;
        } else {
            return 0;
        }
    }

    public function getFnYear($depid) {
        $sql = "SELECT TOP 1 Fortnight_year FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ?
                AND (CAST(DATEADD(YEAR, 543, GETDATE()) AS DATE) BETWEEN OpenDate AND CloseDate) ORDER BY Fortnight_year DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->Fortnight_year;
        } else {
            return 0;
        }
    }

    public function getWorkDetail($fno, $fyear, $dep, $teamid) {
        $sql = "SELECT wd.DetailID, wd.TeamID, wd.EmpID, wd.EmpName, wd.SaleCode, wd.LeadCheckTime, wd.SupCheckTime,
                wd.CitizenID, CONVERT(varchar(10), wd.PaymentAmount) AS PayAmount,
                wd.LeadApproveStatus,
                CASE
                  WHEN wd.SaleCode IS NULL THEN 0
                  ELSE
                	(CASE WHEN DATEDIFF(dd,DATEADD(dd,-1,sl.StartDate),GETDATE()) >= 31 THEN 1 ELSE (CASE WHEN sl.saleempType = 1 THEN 0 ELSE 1 END) END)
                END AS saletype,
                CASE
                  WHEN wd.SaleCode IS NULL THEN NULL
                  ELSE
                	DATEADD(day,31,sl.StartDate)
                END AS TurnproDate,
                wd.Image,
                REPLACE(wd.EmpID,'A','0') AS EmpImage,
                CASE
                  WHEN wd.SupCheckTime IS NULL THEN wd.LeadApproveStatus
                	ELSE wd.SupApproveStatus
                END AS SwitchStatus
                FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS wd
                LEFT JOIN TSR_Application.dbo.NPT_Sale_Log AS sl ON sl.FnNo = ? AND sl.FnYear = ? AND sl.DepID = ? AND sl.SaleCode = wd.SaleCode
                          AND sl.SaleStatus IN ('N', 'P','D') AND sl.PositID NOT IN ('65','85') AND sl.PosID < 3
                WHERE wd.TeamID = ? ORDER BY wd.DetailID";
        $stmt = $this->db->query($sql, array($fno, $fyear, $dep, $teamid));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return null;
        }
    }

    public function getWorkDetail_lockCostBranch($fno, $fyear, $dep, $teamid) {
        $sql = "SELECT wd.DetailID, wd.TeamID, wd.EmpID, wd.EmpName, wd.SaleCode, wd.LeadCheckTime, wd.SupCheckTime,
                wd.CitizenID, CONVERT(varchar(10), wd.PaymentAmount) AS PayAmount,
                wd.LeadApproveStatus,
                CASE
                  WHEN wd.SaleCode IS NULL THEN 0
                  ELSE
                	(CASE WHEN DATEDIFF(dd,DATEADD(dd,-1,sl.StartDate),GETDATE()) >= 31 THEN 1 ELSE (CASE WHEN sl.saleempType = 1 THEN 0 ELSE 1 END) END)
                END AS saletype,
                CASE
                  WHEN wd.SaleCode IS NULL THEN NULL
                  ELSE
                	DATEADD(day,31,sl.StartDate)
                END AS TurnproDate,
                wd.Image,
                REPLACE(wd.EmpID,'A','0') AS EmpImage,
                wd.SupApproveStatus AS SwitchStatus
                FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS wd
                LEFT JOIN TSR_Application.dbo.NPT_Sale_Log AS sl ON sl.FnNo = ? AND sl.FnYear = ? AND sl.DepID = ? AND sl.SaleCode = wd.SaleCode
                          AND sl.SaleStatus IN ('N', 'P','D') AND sl.PositID NOT IN ('65','85') AND sl.PosID < 3
                WHERE wd.TeamID = ? ORDER BY wd.DetailID";
        $stmt = $this->db->query($sql, array($fno, $fyear, $dep, $teamid));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return null;
        }
    }

    public function getCostBranch() {
        $date = date('d-m-Y');
        $sql = "SELECT CONVERT(varchar, LockDate, 105), IsActive FROM Allowance.dbo.CostBranch_LockData
                WHERE CONVERT(varchar, LockDate, 105) = ? AND IsActive = 1";
        $stmt = $this->db->query($sql, array($date));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getApproveStatus($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT SupApproveStatus FROM TSR_DB1.dbo.SaleTeam_Work
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

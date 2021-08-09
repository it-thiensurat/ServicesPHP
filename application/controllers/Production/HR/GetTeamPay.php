<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetTeamPay extends REST_Controller
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
        if ($this->getCostBranch() == 1) {
            $sql = "SELECT TeamID,TeamCode,FnYear,FnNo,DepID,LeadCheckTime,LeadCheckNum,LeadCheckWorkNum,LeadCheckOutNum,EmpID
                    FROM TSR_DB1.dbo.SaleTeam_Work
                    WHERE TeamCode = ? AND DepID = ? AND FnYear = ? AND FnNo = ?
                    AND SupApproveStatus = 1 AND CONVERT(VARCHAR(10),LeadCheckTime,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
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
                        'WorkDetail'        => $this->getWorkDetail($v["FnNo"], $v["FnYear"], $v["DepID"], $v["TeamID"])
                    );

                    array_push($data, $r);
                }

                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "รายการลงเวลา",
                        "data"      => $data
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
        $sql = "SELECT swd.DetailID, swd.TeamID, swd.EmpID, swd.EmpName, swd.SaleCode, swd.SupCheckTime, swd.CitizenID,
                    swd.SupApproveStatus, swd.Image, swd.PaymentStatus,
                    CONVERT(varchar, PaymentAmount) AS PaymentAmount,
                    CONVERT(varchar, swd.PaymentBalance) AS PaymentBalance,
                    CASE
                       WHEN swd.SaleCode IS NULL THEN 0
                       ELSE
                    	(CASE WHEN DATEDIFF(dd,DATEADD(dd,-1,sl.StartDate),GETDATE()) >= 31 THEN 1 ELSE (CASE WHEN sl.saleempType = 1 THEN 0 ELSE 1 END) END)
                    END AS saletype,
                    CASE
                      WHEN swd.SaleCode IS NULL THEN NULL
                      ELSE
                    	DATEADD(day,31,sl.StartDate)
                    END AS TurnproDate,
                    (
                        SELECT TOP 1 stwp.PaymentImage
                        FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd
                        LEFT JOIN TSR_DB1.dbo.SaleTeam_Work_Payment AS stwp ON stwd.DetailID = stwp.DetailID AND stwp.CitizenID = swd.CitizenID
                        WHERE stwd.TeamID = ? ORDER BY stwp.PaymentID DESC
                    ) AS PaymentImage
                FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS swd
                LEFT JOIN TSR_Application.dbo.NPT_Sale_Log AS sl ON sl.FnNo = ? AND sl.FnYear = ? AND sl.DepID = ? AND sl.SaleCode = swd.SaleCode
                          AND sl.SaleStatus IN ('N', 'P','D') AND sl.PositID NOT IN ('65','85') AND sl.PosID < 3
                WHERE swd.TeamID = ? AND swd.SupApproveStatus = 1 ORDER BY swd.DetailID";
        $stmt = $this->db->query($sql, array($teamid, $fno, $fyear, $dep, $teamid));
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $d = array(
                    'DetailID'          => $v['DetailID'],
                    'TeamID'            => $v['TeamID'],
                    'EmpID'             => $v['EmpID'],
                    'EmpName'           => $v['EmpName'],
                    'SaleCode'          => $v['SaleCode'],
                    'SupCheckTime'      => $v['SupCheckTime'],
                    'CitizenID'         => $v['CitizenID'],
                    'saletype'          => $v['saletype'],
                    'TurnproDate'       => $v['TurnproDate'],
                    'SupApproveStatus'  => $v['SupApproveStatus'],
                    'Image'             => $v['PaymentStatus'] == 0 ? str_replace('A','0',$v['EmpID']) : $v['PaymentImage'],
                    'PaymentAmount'     => $v['PaymentAmount'],
                    'PaymentCompare'    => $v['PaymentAmount'],
                    'PaymentBalance'    => $v['PaymentBalance'],
                    'PaymentStatus'     => $v['PaymentStatus']
                );

                array_push($data, $d);
            }
            return $data;
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
}

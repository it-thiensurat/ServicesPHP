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
        $fnyear     = $this->input->post('fnyear');
        $fnno       = $this->input->post('fnno');
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
                        'WorkDetail'        => $this->getWorkDetail($v["TeamID"], $v["EmpID"])
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

    public function getWorkDetail($teamid, $empid) {
        // $sql = "SELECT swd.DetailID, swd.TeamID, swd.EmpID, swd.EmpName, swd.SaleCode, swd.SupCheckTime, swd.CitizenID,
        //         swd.SupApproveStatus, swd.Image, swd.PaymentStatus, swp.PaymentImage,
        //         CONVERT(varchar, (200 - swd.PaymentBalance)) AS PaymentAmount,
        //         CONVERT(varchar, swd.PaymentBalance) AS PaymentBalance
        //         FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS swd
        //         LEFT JOIN (
        //             SELECT TOP 1 stwd.DetailID, stwd.EmpID, ISNULL(stwp.PaymentImage, '') AS PaymentImage
        //             FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd
        //             LEFT JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Payment AS stwp ON stwd.DetailID = stwp.DetailID AND stwp.EmpID = '" . $empid . "'
        //             WHERE stwd.TeamID = " . $teamid . " ORDER BY stwp.PaymentID DESC
        //         ) AS swp ON swp.DetailID = swd.DetailID AND swp.EmpID = '" . $empid . "'
        //         WHERE swd.TeamID = " . $teamid . " AND swd.SupApproveStatus = 1";
        $sql = "SELECT swd.DetailID, swd.TeamID, swd.EmpID, swd.EmpName, swd.SaleCode, swd.SupCheckTime, swd.CitizenID,
                    swd.SupApproveStatus, swd.Image, swd.PaymentStatus,
                    CONVERT(varchar, PaymentAmount) AS PaymentAmount,
                    CONVERT(varchar, swd.PaymentBalance) AS PaymentBalance,
                    (
                        SELECT TOP 1 stwp.PaymentImage
                        FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd
                        LEFT JOIN TSR_DB1.dbo.SaleTeam_Work_Payment AS stwp ON stwd.DetailID = stwp.DetailID AND stwp.EmpID = swd.EmpID
                        WHERE stwd.TeamID = $teamid ORDER BY stwp.PaymentID DESC
                    ) AS PaymentImage
                FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS swd
                WHERE swd.TeamID = $teamid AND swd.SupApproveStatus = 1";
        $stmt = $this->db->query($sql);
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

<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class PaymentConfirm extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $date           = date('Y-m-d H:i:s');
        $teamId         = $this->input->post('teamid');
        $fnno           = $this->input->post('fnno');
        $fnyear         = $this->input->post('fnyear');
        $depId          = $this->input->post('depid');
        $empId          = $this->input->post('empid');
        $paymentType    = $this->input->post('paymenttype');

        if ($paymentType == "100") {
            $sql = "UPDATE TSR_DB1.dbo.SaleTeam_Work100 SET PaymentCheckNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work100 AS sw
                        LEFT JOIN TSR_DB1.dbo.SaleTeam_Work100_Detail AS stwd ON sw.TeamID = stwd.TeamID
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentApproveNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work100 AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work100_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 1 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentNotApproveNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work100 AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work100_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 0 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentAmount = (
                        SELECT SUM(stwd.PaymentAmount) AS total FROM TSR_DB1.dbo.SaleTeam_Work100 AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work100_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentBalance = (
                        SELECT SUM(stwd.PaymentBalance) AS total FROM TSR_DB1.dbo.SaleTeam_Work100 AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work100_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 1 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentTime = ?, PaymentStatus = 1, UpdateDate = ?, UpdateBy = ?
                    WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10), CreateDate, 126) = CONVERT(VARCHAR(10),GETDATE(),126)";
            $stmt = $this->db->query($sql, array($date, $date, $empId, $teamId, $fnyear, $fnno, $depId));
        } else {
            $sql = "UPDATE TSR_DB1.dbo.SaleTeam_Work SET PaymentCheckNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                        LEFT JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentCheckWorkNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 1 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentCheckOutNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 0 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentAmount = (
                        SELECT SUM(stwd.PaymentAmount) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentBalance = (
                        SELECT SUM(stwd.PaymentBalance) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.PaymentStatus = 1 AND stwd.SupApproveStatus = 1
                        WHERE sw.TeamID = " . $teamId . " AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depId . "
                    ), PaymentTime = ?, PaymentStatus = 1, UpdateDate = ?, UpdateBy = ?
                    WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10), CreateDate, 126) = CONVERT(VARCHAR(10),GETDATE(),126)";
            $stmt = $this->db->query($sql, array($date, $date, $empId, $teamId, $fnyear, $fnno, $depId));
        }
        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "บันทึกข้อมูลการจ่ายเงินเรียบร้อยแล้ว",
                    "data"      => ""
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถอัพเดทข้อมูลได้",
                    "data"      => ""
                ), 200
            );
        }
    }

}

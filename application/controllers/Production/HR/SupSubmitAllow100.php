<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class SupSubmitAllow100 extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $teamid     = $this->input->post('teamid');
        $empid      = $this->input->post('empid');
        $lat        = $this->input->post('latitude');
        $lon        = $this->input->post('longitude');
        $teamlist   = json_decode($this->input->post('teamlist'), true);
        $date       = date('Y-m-d H:i:s');

        $sql = "UPDATE TSR_DB1.dbo.SaleTeam_Work100_Detail SET SupCheckTime = ?, SupApproveStatus = ?, Latitude = ?, Longitude = ?, 
                UpdateDate = ?, UpdateBy = ?, SupCause = ?
                WHERE DetailID = ?";
        foreach($teamlist as $k => $v) {
            if ($v["SwitchStatus"] == 0 && $v["CauseStatus"] == 0) {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "กรุณาระบุเหตุผลที่ไม่อนุมัติ",
                        "data"      => ""
                    ), 200
                );
                exit();
            } else if ($v["SwitchStatus"] == 1 && $v["CauseStatus"] > 0) {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "กรณีอนุมัติไม่ต้องระบุเหตุผล",
                        "data"      => ""
                    ), 200
                );
                exit();
            }

            $stmt = $this->db->query($sql, array($date, $v["SwitchStatus"], $lat, $lon, $date, $empid, $v["CauseStatus"], $v['DetailID']));
            // if ($stmt) {
            $issave = $v["SwitchStatus"] == 1 ? 3 : 2;
            $cause = $v["CauseStatus"] == 0 ? "" : $v["CauseStatus"];
            $stmt = $this->updateToCostBranch($v["CitizenID"], $v["FnNo"], $v["FnYear"], $v["PaymentAmount"],  $issave, $empid, $cause);
            // }
        }

        // if ($this->db->affected_rows() > 0) {
            $sql = "UPDATE TSR_DB1.dbo.SaleTeam_Work100 SET SupCheckNum = (
                        SELECT COUNT(TeamID) FROM TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . "
                    ), SupApproveNum = (
                        SELECT COUNT(TeamID) FROM TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . " AND SupApproveStatus = 1
                    ), SupNotApproveNum = (
                        SELECT COUNT(TeamID) FROM TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . " AND SupApproveStatus = 0
                    ), SupCheckTime = ?, SupApproveStatus = 1, UpdateDate = ?, UpdateBy = ?
                    WHERE TeamID = ?";
            $stmt = $this->db->query($sql, array($date, $date, $empid, $teamid));
            if ($stmt) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกข้อมูลอนุมัติจ่ายเงินเรียบร้อยแล้ว",
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
        // }
    }

    public function updateToCostBranch($citizen, $fnno, $fnyear, $amount, $approvestatus, $empid, $causestatus) {
        $sql = "exec TSR_Application.dbo.CostBranch_Manage_Allowance_100 @citizenid = ?,
                    @FnNo = ?, @FnYear = ?, @amount = ?, @isSave = ?, @createBy = ?, @remark = ?";
        $stmt = $this->db->query($sql, array($citizen, $fnno, $fnyear, $amount, $approvestatus, $empid, $causestatus));
        return $stmt;
    }
}
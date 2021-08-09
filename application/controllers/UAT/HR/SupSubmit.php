<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class SupSubmit extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $empId          = $this->input->post('empid');
        $team_check     = json_decode($this->input->post('teamcheck'), true);
        $lat            = $this->input->post('latitude');
        $lon            = $this->input->post('longitude');
        $date           = date('Y-m-d H:i:s');
        $approve_status = 1;
        $WorkDetail     = [];
        $teamId         = "";

        foreach($team_check as $k => $j) {
            $teamId     = $j['TeamID'];
            $teamno     = $j['TeamCode'];
            $fnno       = $j['FnNo'];
            $fnyear     = $j['FnYear'];
            $depid      = $j['DepID'];
            $WorkDetail = $j['WorkDetail'];

            $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail SET SupCheckTime = ?, SupApproveStatus = ?, UpdateDate = ?, UpdateBy = ?
                    WHERE DetailID = ? AND TeamID = ?";
            foreach($WorkDetail as $k => $v) {
                $teamId     = $v['TeamID'];
                $detailId   = $v['DetailID'];
                $status     = $v['SwitchStatus'];

                $stmt = $this->db->query($sql, array($date, $status, $date, $empId, $detailId, $teamId));
            }

            if ($this->db->affected_rows() > 0) {
                $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work SET SupCheckNum = (
                            SELECT COUNT(stwd.TeamID) AS total FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                            LEFT JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID
                            WHERE sw.TeamID = '" . $teamId . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                        ), SupCheckWorkNum = (
                            SELECT COUNT(stwd.TeamID) AS approve FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                            INNER JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.SupApproveStatus = 1
                            WHERE sw.TeamID = '" . $teamId . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                        ), SupCheckOutNum = (
                            SELECT COUNT(stwd.TeamID) AS unapprove FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                            INNER JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.SupApproveStatus = 0
                            WHERE sw.TeamID = '" . $teamId . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                        ), SupCheckTime = ?, SupApproveStatus = ?, Latitude = ?, Longitude = ?, UpdateDate = ?, UpdateBy = ?
                        WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10),CreateDate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
                $stmt   = $this->db->query($sql, array($date, $approve_status, $lat, $lon, $date, $empId, $teamId, $fnyear, $fnno, $depid));
                if ($stmt) {
                    $this->response(
                        array(
                            "status"    => "SUCCESS",
                            "message"   => "บันทึกข้อมูลอนุมัติทีมขายเรียบร้อยแล้ว",
                            "data"      => ""
                        ), 200
                    );
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถอัพเดทข้อมูลได้1",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถอัพเดทข้อมูลได้2",
                        "data"      => ""
                    ), 200
                );
            }
        }
    }

    public function AddToZktime($workdetail) {
        $date           = date('Y-m-d H:i:s');

        foreach($workdetail as $k => $v) {
            $status     = $v['SwitchStatus'];
            $checkTime = $v['LeadCheckTime'] != null ? date('Y-m-d H:i:s', strtotime($v['LeadCheckTime'])) : $date;

            $data = array(
                'USERID'        => $this->cleanLetters($v['EmpID']),
                'CHECKTIME'     => $checkTime,
                'CHECKTYPE'     => 'I',
                'VERIFYCODE'    => 1,
                'SENSORID'      => 999,
                'WorkCode'      => 0,
                'sn'            => null,
                'UserExtFmt'    => 0,
                'CitizenId'     => $v['CitizenID'],
                'empid'         => $v['EmpID'],
                'createdDate'   => $date,
            );

            if ($status == 1) {
                $this->db->insert('SQLUAT.ZKTimeData.dbo.CHECKINOUT', $data);
            } else {
                $userid = $this->cleanLetters($v['EmpID']);
                $sql = "DELETE FROM SQLUAT.ZKTimeData.dbo.CHECKINOUT
                        WHERE USERID = '" . $userid . "' AND CitizenId = '" . $v['CitizenID'] . "' AND empid = '" . $v['EmpID'] . "' AND SENSORID = '999' AND CONVERT(varchar, createdDate , 105) = CONVERT(varchar, GETDATE(), 105)";
                $stmt = $this->db->query($sql);
            }
        }
    }

    public function cleanLetters($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }
}

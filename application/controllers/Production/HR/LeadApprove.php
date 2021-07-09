<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class LeadApprove extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_post() {
        $empId          = $this->input->post('empid');
        $teamid         = $this->input->post('teamId');
        $teamno         = $this->input->post('teamNo');
        $depid          = $this->input->post('depid');
        $fnno           = $this->input->post('fnno');
        $fnyear         = $this->input->post('fnyear');
        $lat            = $this->input->post('latitude');
        $lon            = $this->input->post('longitude');
        $team_list      = json_decode($this->input->post('teamlist'), true);
        $date           = date('Y-m-d H:i:s');
        $approve_status = 1;

        $sql = "UPDATE TSR_DB1.dbo.SaleTeam_Work_Detail SET LeadCheckTime = ?, Latitude = ?, Longitude = ?, LeadApproveStatus = ?, PaymentAmount = ?, UpdateDate = ?, UpdateBy = ?
                WHERE DetailID = ? AND TeamID = ?";
        foreach($team_list as $k => $v) {
                $id         = $v['teamId'];
                $detailId   = $v['detailId'];
                $pay        = $v['LeadApproveStatus'] == 0 ? '0' : $v['PayAmount'];
                $time       = $v['CheckTime'] == 0 ? NULL : $date;
                $latit      = $v['CheckTime'] == 0 ? NULL : $lat;
                $longi      = $v['CheckTime'] == 0 ? NULL : $lon;
                $status     = $v['LeadApproveStatus'];

                $stmt = $this->db->query($sql, array($time, $latit, $longi, $status, $pay, $date, $empId, $detailId, $id));
        }

            if ($this->db->affected_rows() > 0) {
               $sql    = "UPDATE TSR_DB1.dbo.SaleTeam_Work SET LeadCheckNum = (
                              SELECT COUNT(stwd.TeamID) AS total FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                              LEFT JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID
                              WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                          ), LeadCheckWorkNum = (
                              SELECT COUNT(stwd.TeamID) AS approve FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                              INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.LeadApproveStatus = 1
                              WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                          ), LeadCheckOutNum = (
                              SELECT COUNT(stwd.TeamID) AS unapprove FROM TSR_DB1.dbo.SaleTeam_Work AS sw
                              INNER JOIN TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.LeadApproveStatus = 0
                              WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                          ), LeadCheckTime = ?, LeadApproveStatus = ?, Latitude = ?, Longitude = ?, UpdateDate = ?, UpdateBy = ?
                           WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10),CreateDate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
               $stmt   = $this->db->query($sql, array($date, $approve_status, $lat, $lon, $date, $empId, $teamid, $fnyear, $fnno, $depid));
               if ($stmt) {
                  $this->response(
                      array(
                          "status"    => "SUCCESS",
                          "message"   => "บันทึกข้อมูลการลงเวลาเรียบร้อยแล้ว",
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

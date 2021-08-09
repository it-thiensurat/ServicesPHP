<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class LeadSubmit extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->img_url = 'http://thiensurat.com/fileshare01';
    }

    public function index_post() {
        $teamid = $this->input->post('teamId');
        $teamno = $this->input->post('teamNo');
        $depid  = $this->input->post('depid');
        $fnno   = $this->input->post('fnno');
        $fnyear = $this->input->post('fnyear');
        $empId  = $this->input->post('empId');
        $lat    = $this->input->post('latitude');
        $lon    = $this->input->post('longitude');
        $date   = date('Y-m-d H:i:s');

        $sql    = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work SET LeadCheckNum = (
                        SELECT COUNT(stwd.TeamID) AS total FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                        LEFT JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID
                        WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                    ), LeadCheckWorkNum = (
                        SELECT COUNT(stwd.TeamID) AS approve FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.LeadApproveStatus = 1
                        WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                    ), LeadCheckOutNum = (
                        SELECT COUNT(stwd.TeamID) AS unapprove FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work AS sw
                        INNER JOIN SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS stwd ON sw.TeamID = stwd.TeamID AND stwd.LeadApproveStatus = 0
                        WHERE sw.TeamID = '" . $teamid . "' AND sw.FnNo = " . $fnno . " AND sw.FnYear = " . $fnyear . " AND sw.DepID = " . $depid . "
                    ), LeadCheckTime = ?, LeadApproveStatus = ?, Latitude = ?, Longitude = ?, UpdateDate = ?, UpdateBy = ?
                     WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10),CreateDate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
        $stmt   = $this->db->query($sql, array($date, 1, $lat, $lon, $date, $empId, $teamid, $fnyear, $fnno, $depid));
        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "ยืนยันข้อมูลการลงเวลา",
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

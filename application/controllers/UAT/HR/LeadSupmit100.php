<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class LeadSupmit100 extends REST_Controller
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

        $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail SET LeadCheckTime = ?, LeadApproveStatus = ?, UpdateDate = ?, UpdateBy = ?, LeadCause = ?
                WHERE DetailID = ?";
        foreach($teamlist as $k => $v) {
            // $data = array(
            //     "LeadCheckTime"     => $date,
            //     "LeadApproveStatus" => $v["SwitchStatus"],
            //     "UpdateDate"        => $date,
            //     "UpdateBy"          => $empid,
            //     "LeadCause"         => $v["CauseStatus"]
            // );

            // $this->db->where('DetailID', $v['detailId']);
            // $this->db->update('TSR_DB1.dbo.SaleTeam_Work100_Detail', $data);
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

            $stmt = $this->db->query($sql, array($date, $v["SwitchStatus"], $date, $empid, $v["CauseStatus"], $v['detailId']));
        }

        if ($this->db->affected_rows() > 0) {
            $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work100 SET LeadCheckNum = (
                        SELECT COUNT(TeamID) FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . "
                    ), LeadApproveNum = (
                        SELECT COUNT(TeamID) FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . " AND LeadApproveStatus = 1
                    ), LeadNotApproveNum = (
                        SELECT COUNT(TeamID) FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail WHERE TeamID = " . $teamid . " AND LeadApproveStatus = 0
                    ), LeadCheckTime = ?, LeadApproveStatus = 1, Latitude = ?, Longitude = ?, UpdateDate = ?, UpdateBy = ?
                    WHERE TeamID = ?";
            $stmt = $this->db->query($sql, array($date, $lat, $lon, $date, $empid, $teamid));
            if ($stmt) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกข้อมูลอนุมัติจ่ายเงินเรียบร้อยแล้ว",
                        "data"      => ""
                    ), 200
                );
            }
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

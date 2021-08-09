<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AddToZKTime extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        echo date('Y-m-d H:i:s');
    }

    public function index_post() {
        $teamlist       = json_decode($this->input->post('workdetail'), true);
        $date           = date('Y-m-d H:i:s');

        $sql = "INSERT INTO ZKTimeData.dbo.CHECKINOUT (USERID, CHECKTIME, CHECKTYPE, VERIFYCODE, SENSORID, WorkCode, UserExtFmt, CitizenId, EmpId, CreateDate)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        foreach($teamlist as $k => $v) {
            $id           = str_replace('A','',$v["EmpID"]);
            $empid        = $v["EmpID"];
            $checktime    = $v["LeadCheckTime"] == NULL ? $date : $v["LeadCheckTime"];
            $status       = $v["SwitchStatus"];
            $citizen      = $v["CitizenID"];

            if ($status) {
                if (!$this->checkEmpHasCheckIn($empid, $citizen)) {
                    $stmt = $this->db->query($sql, array($id, $checktime, 'I', 1, '999', 0, 0, $citizen, $empid, $date));
                }
            } else {
                if ($this->checkEmpHasCheckIn($empid, $citizen)) {
                    $sql2 = "DELETE FROM ZKTimeData.dbo.CHECKINOUT
                             WHERE EmpId = ? AND CitizenId = ? AND SENSORID = '999' AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
                    $stmt2 = $this->db->query($sql2, array($empid, $citizen));
                }
            }
        }
    }

    public function checkEmpHasCheckIn($empid, $citizen) {
        $sql = "SELECT * FROM ZKTimeData.dbo.CHECKINOUT
                WHERE EmpId = ? AND CitizenId = ? AND SENSORID = '999' AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($empid, $citizen));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}

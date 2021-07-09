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
        echo date('Y-m-d H:i:s', strtotime('2021-03-16 14:05:41.000'));
    }

    public function index_post() {
        $workdetail     = json_decode($this->input->post('workdetail'), true);
        $date           = date('Y-m-d H:i:s');

        foreach($workdetail as $k => $v) {
            $status     = $v['SwitchStatus'];
            $checkTime  = "";

            if (is_null($v['LeadCheckTime'])) {
                $checkTime = $date;
            } else {
                $checkTime = date('Y-m-d H:i:s', strtotime($v['LeadCheckTime']));
            }

            if ($status === 1) {
                $data = array(
                    'USERID'        => $this->cleanLetters($v['EmpID']),
                    'CHECKTIME'     => $checkTime,
                    'CHECKTYPE'     => 'I',
                    'VERIFYCODE'    => 1,
                    'SENSORID'      => '999',
                    'WorkCode'      => 0,
                    'sn'            => null,
                    'UserExtFmt'    => 0,
                    'CitizenId'     => $v['CitizenID'],
                    'empid'         => $v['EmpID'],
                    'createdDate'   => $date,
                );
                if (!$this->checkEmpHasCheckIn($v['EmpID'], $v['CitizenID'])) {
                    $this->db->insert('ZKTimeData.dbo.CHECKINOUT', $data);
                }
            } else if ($status === 0) {
                if ($this->checkEmpHasCheckIn($v['EmpID'], $v['CitizenID'])) {
                    $userid = $this->cleanLetters($v['EmpID']);
                    $sql = "DELETE FROM ZKTimeData.dbo.CHECKINOUT 
                            WHERE USERID = ? AND CitizenId = ? AND empid = ? AND SENSORID = ? AND CONVERT(varchar, createdDate , 105) = CONVERT(varchar, GETDATE(), 105)";
                    $stmt = $this->db->query($sql, array($userid, $v['CitizenID'], $v['EmpID'], '999'));
                }
            }
        }
    }

    public function checkEmpHasCheckIn($empid, $cardid) {
        $sql = "SELECT * FROM ZKTimeData.dbo.CHECKINOUT 
                WHERE USERID = ? AND CitizenId = ? AND empid = ? AND CONVERT(varchar, createdDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($this->cleanLetters($empid), $cardid, $empid));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function cleanLetters($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }
}
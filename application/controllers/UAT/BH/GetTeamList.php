<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class GetTeamList extends REST_Controller { 
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->jwt = new CreatorJwt();
        $this->authorization = '';
        $this->apiKey = $this->config->item('key_token');
        try {
            $author = false;
            $token = $this->input->request_headers();
            $apiKey = '';
            foreach($token as $key => $value) {
                if ($key == 'Authorization') {
                    $this->authorization = $value;
                }

                if ($key == 'X-Api-Key') {
                    $apiKey = $value;
                }
            }

            if ($apiKey != '') {
                if (!$this->ApiModel->checkApiKey($apiKey)) {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "Key สำหรับเรียกใช้ api ไม่ถูกต้อง",
                            "token"     => "",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "กรุณาระบุ Key สำหรับเรียกใช้ Api.",
                        "token"     => "",
                        "data"      => ""
                    ), 200
                );
            }
        } catch(Exception $e) {
            $output = array("Exception" => $e->getMessage());
            $this->response($output, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function index_post() {
        $teamno = $this->input->post('teamNo');
        $depid  = $this->input->post('depid');
        $fnno   = $this->input->post('fnno');
        $fnyear = $this->input->post('fnyear');
        $empId  = $this->input->post('empId');

        $sql = "SELECT SL.saleemp, SL.salecode, SL.FName + ' ' + SL.LName as Fullname, SM.CitizenID
                FROM SQLUAT.TSR_Application.dbo.NPT_Sale_Log AS SL WITH(NOLOCK)
                LEFT JOIN SQLUAT.TSR_Application.dbo.NPT_Sale_Main AS SM ON SL.SaleID = SM.SaleID
                WHERE ISNULL(LEFT(SL.Salecode,4), '-')+ISNULL(cast(SL.TeamNo AS varchar(4)),'-') = ?
                AND SL.PosID < 3 AND SL.TeamNo IS NOT NULL AND SL.SaleStatus != 'R' AND SL.DepID = ? 
                AND SL.FnYear = ? AND SL.FnNo = ? AND SL.PositID NOT IN ('65','85')"; //AND ISNULL(SL.SaleEmpType1, 0) != 8";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnyear, $fnno));
        if ($stmt->num_rows() > 0 || $stmt->result_array() != "") {
            $this->createMaster($teamno, $depid, $fnno, $fnyear, $stmt->result_array(), $empId);
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบข้อมูลสายงาน",
                    "data"      => $stmt
                ), 200
            );
        }
    }

    public function createMaster($teamno, $depid, $fnno, $fnyear, $teamlist, $empId) {
        $date = date('Y-m-d H:i:s');
        $leadStatus;
        $data = array(
            'TeamCode'              => $teamno,
            'FnYear'                => $fnyear,
            'FnNo'                  => $fnno,
            'DepID'                 => $depid,
            'LeadCheckNum'          => 0,
            'LeadCheckWorkNum'      => 0,
            'LeadCheckOutNum'       => 0,
            'LeadApproveStatus'     => 0,
            'SupCheckNum'           => 0,
            'SupCheckWorkNum'       => 0,
            'SupCheckOutNum'        => 0,
            'SupApproveStatus'      => 0,
            'PaymentCheckNum'       => 0,
            'PaymentCheckWorkNum'   => 0,
            'PaymentCheckOutNum'    => 0,
            'PaymentAmount'         => 0,
            'PaymentStatus'         => 0,
            'CreateDate'            => $date,
            'CreateBy'              => 'App',
            'EmpID'                 => $empId
        );

        if (!$this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {
            $stmt = $this->db->insert('SQLUAT.TSR_DB1.dbo.SaleTeam_Work', $data);
            if ($stmt) {
                $id = $this->last_insert_id();
                foreach($teamlist as $k => $v) {
                    $detail = array(
                        'TeamID'                => $id,
                        'EmpID'                 => $v['saleemp'],
                        'EmpName'               => $v['Fullname'],
                        'SaleCode'              => $v['salecode'],
                        "CitizenID"             => $v['CitizenID'],
                        'LeadApproveStatus'     => 0,
                        'SupApproveStatus'      => 0,
                        'PaymentAmount'         => 0,
                        'PaymentStatus'         => 0,
                        'CreateDate'            => $date,
                        'CreateBy'              => 'App'
                    );

                    $stmt = $this->db->insert('SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail', $detail);
                }

                if ($this->db->affected_rows() > 0) {
                    $sql = "SELECT * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail AS swd WHERE swd.TeamID = ?";
                    $stmt = $this->db->query($sql, array($id));
                    $team = [];
                    foreach($stmt->result_array() as $k => $v) {
                        $t = array(
                            "teamId"            => $id,
                            "detailId"          => $v['DetailID'],
                            "saleemp"           => $v["EmpID"],
                            "salecode"          => $v["SaleCode"],
                            "Fullname"          => $v["EmpName"],
                            "CitizenID"         => $v['CitizenID'],
                            "PayAmount"         => $v['PaymentAmount'] == 0 ? '200' : $v['PaymentAmount'],
                            "LeadApproveStatus" => $v["LeadApproveStatus"],
                            "LeadCheckTime"     => $v["LeadCheckTime"],
                            "CostBranch"        => $this->getCostBranch(),
                            "SaleImage"         => $v["Image"] == NULL ? "" : $v["Image"],
                        );

                        array_push($team, $t);
                    }
                    $this->response(
                        array(
                            "status"                => "SUCCESS",
                            "message"               => "ข้อมูลสายงาน",
                            "CostBranch"            => $this->getCostBranch(),
                            "LeadApproveStatus"     => $this->gerApproveStatus($teamno, $depid, $fnno, $fnyear),
                            "data"                  => $team
                        ), 200
                    );
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถเตรียมข้อมูลสายงาน",
                            "data"      => ""
                        ), 200
                    );
                }
            }
        } else {
            $team = [];
            $leadStatus;
            $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail stwd 
                    WHERE stwd.EmpID = ? AND stwd.SaleCode = ? 
                    AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
            foreach($teamlist as $k => $v) {
                $stmt = $this->db->query($sql, array($v["saleemp"], $v["salecode"]));
                $t = array(
                    "teamId"            => $stmt->row()->TeamID,
                    "detailId"          => $stmt->row()->DetailID,
                    "saleemp"           => $v["saleemp"],
                    "salecode"          => $v["salecode"],
                    "Fullname"          => $v["Fullname"],
                    "CitizenID"         => $v['CitizenID'],
                    "PayAmount"         => $stmt->row()->PaymentAmount == 0 ? '200' : strval($stmt->row()->PaymentAmount),
                    "LeadApproveStatus" => $stmt->row()->LeadApproveStatus,
                    "LeadCheckTime"     => null,
                    "CostBranch"        => $this->getCostBranch(),
                    "SaleImage"         => $stmt->row()->Image == NULL ? "" : $stmt->row()->Image,
                );

                array_push($team, $t);
            }

            $this->response(
                array(
                    "status"                => "SUCCESS",
                    "message"               => "ข้อมูลสายงาน",
                    "CostBranch"            => $this->getCostBranch(),
                    "LeadApproveStatus"     => $this->gerApproveStatus($teamno, $depid, $fnno, $fnyear),
                    "data"                  => $team
                ), 200
            );
        }
    }

    public function checkCreateMaster($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ? 
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function last_insert_id() {
        $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work ORDER BY TeamID DESC";
        $stmt = $this->db->query($sql);
        return $stmt->row()->TeamID;
    }

    public function getCostBranch() {
        $date = date('d-m-Y');
        $sql = "SELECT CONVERT(varchar, LockDate, 105), IsActive FROM SQLUAT.TSR_Application.dbo.CostBranch_LockData 
                WHERE CONVERT(varchar, LockDate, 105) = ? AND IsActive = 1";
        $stmt = $this->db->query($sql, array($date));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function gerApproveStatus($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT LeadApproveStatus FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ? 
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->LeadApproveStatus;
        } else {
            return 0;
        }
    }
}
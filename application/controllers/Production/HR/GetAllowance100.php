<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetAllowance100 extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // public function index_post() {
    //     $checkDate  = '2021-03-22';//date('Y-m-d');
    //     $empid      = $this->input->post('empid');
    //     $fnyear     = $this->input->post('fnyear');
    //     $fnno       = $this->input->post('fnno');
    //     $depid      = $this->input->post('depid');
    //     $teamNo     = $this->input->post('teamno');
    //     $sql        = "exec SQLUAT.TSR_Application.[dbo].[CostBranch_Allowance_100] @checkDate = ?, @TeamNo = ?";
    //     $stmt       = $this->db->query($sql, array($checkDate, $teamNo));

    //     // print_r($stmt->result_array());
    //     // exit();
    //     if ($stmt->num_rows() > 0 || $stmt->result_array() != "") {
    //         $this->createMasterData($teamNo, $empid, $fnyear, $fnno, $depid, $stmt->result_array());
    //     } else {
    //         $this->response(
    //             array(
    //                 "status"    => "FAILED",
    //                 "message"   => "ไม่พบรายการ",
    //                 "data"      => ""
    //             ), 200
    //         );
    //     }
    // }

    public function index_get() {
        $checkDate  = date('Y-m-d H:i:s', strtotime($this->input->get('checkdate')));
        $citizen    = $this->input->get('citizen');
        $this->getAmountPay($checkDate, $citizen);
    }

    public function index_post() {
        // $checkDate  = $this->input->post('checkdate');//'2021-03-22';//date('Y-m-d');
        $empid      = $this->input->post('empid');
        $fnyear     = $this->input->post('fnyear');
        $fnno       = $this->input->post('fnno');
        $depid      = $this->input->post('depid');
        $teamno     = str_replace("-", "", $this->input->post('teamno'));
        $paydate    = "";

        // if ($checkDate == "") {
        //     $paydate  = '2021-03-22 00:00:00.000';
        // } else {
        //     $paydate  = date('Y-m-d H:i:s', strtotime($checkDate));
        // }
        $paydate  = date('Y-m-d H:i:s');

        $sql = "SELECT DISTINCT SL.saleemp, SL.salecode, SL.FName + ' ' + SL.LName as Fullname, SM.CitizenID
                FROM TSR_Application.dbo.NPT_Sale_Log AS SL WITH(NOLOCK)
                LEFT JOIN TSR_Application.dbo.NPT_Sale_Main AS SM ON SL.SaleID = SM.SaleID
                WHERE ISNULL(LEFT(SL.Salecode,4), '-')+ISNULL(cast(SL.TeamNo AS varchar(4)),'-') = ?
                AND SL.PosID < 3 AND SL.TeamNo IS NOT NULL AND SL.SaleStatus != 'R' AND SL.DepID = ?
                AND SL.FnYear = ? AND SL.FnNo = ? AND SL.PositID NOT IN ('65','85')"; //AND ISNULL(SL.SaleEmpType1, 0) != 8";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnyear, $fnno));
        if ($stmt->num_rows() > 0 || $stmt->result_array() != "") {
            $this->createMasterData($teamno, $empid, $fnyear, $fnno, $depid, $stmt->result_array(), $paydate);
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

    public function getReason() {
        $sql = "SELECT id, causeName FROM TSR_Application.dbo.CostBranch_CauseMaster";
        $stmt = $this->db->query($sql);
        if ($stmt) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function createMasterData($teamno, $empid, $fnyear, $fnno, $depid, $teamlist, $paydate) {
        $date = date('Y-m-d H:i:s');
        $data = array(
            'TeamCode'              => $teamno,
            'EmpID'                 => $empid,
            'FnYear'                => $fnyear,
            'FnNo'                  => $fnno,
            'DepID'                 => $depid,
            'LeadCheckNum'          => 0,
            'LeadApproveNum'        => 0,
            'LeadNotApproveNum'     => 0,
            'LeadApproveStatus'     => 0,
            'SupCheckNum'           => 0,
            'SupApproveNum'         => 0,
            'SupNotApproveNum'      => 0,
            'SupApproveStatus'      => 0,
            'PaymentCheckNum'       => 0,
            'PaymentApproveNum'     => 0,
            'PaymentNotApproveNum'  => 0,
            'PaymentAmount'         => 0,
            'PaymentBalance'        => 0,
            'PaymentStatus'         => 0,
            'CreateDate'            => $date,
            'CreateBy'              => $empid
        );
        if (!$this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {
            $id = "0";
            $stmt = $this->db->insert('TSR_DB1.dbo.SaleTeam_Work100', $data);
            if ($stmt) {
                $stmt = $this->db->query("SELECT CONVERT(varchar, MAX(TeamID)) AS TeamID FROM TSR_DB1.dbo.SaleTeam_Work100");
                $id = $stmt->row()->TeamID;
                foreach($teamlist as $k => $v) {
                    $result = $this->getAmountPay($paydate, $v["CitizenID"]);
                    if ($result) {
                        $data = array(
                            'TeamID'            => $id,
                            'EmpID'             => $v["saleemp"],
                            'EmpName'           => $v["Fullname"],
                            'SaleCode'          => $v["salecode"],
                            'CitizenID'         => $v["CitizenID"],
                            'FnYear'            => $result->FnYear,
                            'FnNo'              => $result->FnNo,
                            'LeadApproveStatus' => 0,
                            'SupApproveStatus'  => 0,
                            'PaymentStatus'     => 0,
                            'PaymentAmount'     => $result->amount,
                            'PaymentBalance'    => 0,
                            'LeadCause'         => 0,
                            'SupCause'          => 0,
                            'CreateDate'        => $date,
                            'CreateBy'          => $empid
                        );
                        if ($result->amount > 0) {
                            $stmt = $this->db->insert('TSR_DB1.dbo.SaleTeam_Work100_Detail', $data);
                        }
                    }
                }

                // if ($this->db->affected_rows() > 0) {
                    $sql = "SELECT * FROM TSR_DB1.dbo.SaleTeam_Work100_Detail AS swd WHERE swd.TeamID = ?";
                    $stmt = $this->db->query($sql, array($id));
                    $team = [];
                    foreach($stmt->result_array() as $k => $v) {
                        $t = array(
                            "detailId"          => $v["DetailID"],
                            "salecode"          => $v["EmpID"],
                            "Fullname"          => $v["EmpName"],
                            "CitizenID"         => $v['CitizenID'],
                            "FnYear"            => $v['FnYear'],
                            "FnNo"              => $v['FnNo'],
                            "LeadApproveStatus" => $v["LeadApproveStatus"],
                            "LeadCheckTime"     => $v["LeadCheckTime"],
                            "PaymentStatus"     => $v["PaymentStatus"],
                            "PaymentAmount"     => $v["PaymentAmount"],
                            "PaymentBalance"    => $v["PaymentBalance"],
                            "SwitchStatus"      => is_null($v["LeadCheckTime"]) ? 1 : $v["LeadApproveStatus"],
                            "CauseStatus"       => is_null($v["LeadCheckTime"]) ? 0 : $v["LeadCause"]
                        );

                        array_push($team, $t);
                    }

                    if (count($team) > 0) {
                        $this->response(
                            array(
                                "status"                => "SUCCESS",
                                "message"               => "รายการจ่ายเงิน 100 บาท",
                                "TeamID"                => $id,
                                'CostBranch'            => $this->getPay(),
                                "LeadApproveStatus"     => $this->getApproveStatus($teamno, $depid, $fnno, $fnyear),
                                "data"                  => $team,
                                "cause"                 => $this->getReason()
                            ), 200
                        );
                    } else {
                        $this->response(
                            array(
                                "status"        => "FAILED",
                                "message"       => "ไม่มีรายการจ่ายเงิน",
                            ), 200
                        );
                    }
                // } else {
                //     $this->response(
                //         array(
                //             "status"    => "FAILED",
                //             "message"   => "ไม่มีรายการชำระเงิน 100 บาท",
                //             "data"      => ""
                //         ), 200
                //     );
                // }
            }
        } else {
            $team = [];
            $teamid = "";
            $sql = "SELECT TOP 1 * FROM TSR_DB1.dbo.SaleTeam_Work100_Detail
                    WHERE CitizenID = ? AND SaleCode = ? AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)
                    ORDER BY CreateDate DESC";
            foreach($teamlist as $k => $v) {
                $result = $this->getAmountPay($paydate, $v["CitizenID"]);
                if ($result) {
                    if ($result->amount > 0) {
                        $stmt = $this->db->query($sql, array($v["CitizenID"], $v["salecode"]));
                        $teamid = $stmt->row()->TeamID;
                            $t = array(
                                "detailId"          => $stmt->row()->DetailID,
                                "salecode"          => $v["salecode"],
                                "Fullname"          => $v["Fullname"],
                                "CitizenID"         => $v['CitizenID'],
                                "FnNo"              => $stmt->row()->FnNo,
                                "FnYear"            => $stmt->row()->FnYear,
                                "LeadApproveStatus" => $stmt->row()->LeadApproveStatus,
                                "LeadCheckTime"     => null,
                                "PaymentStatus"     => $stmt->row()->PaymentStatus,
                                "PaymentAmount"     => $stmt->row()->PaymentAmount,
                                "PaymentBalance"    => $stmt->row()->PaymentBalance,
                                "SwitchStatus"      => is_null($stmt->row()->LeadCheckTime) ? 1 : $stmt->row()->LeadApproveStatus,
                                "CauseStatus"       => is_null($stmt->row()->LeadCheckTime) ? 0 : $stmt->row()->LeadCause
                            );
                        array_push($team, $t);
                    }
                }
            }

            if (count($team) > 0) {
                $this->response(
                    array(
                        "status"                => "SUCCESS",
                        "message"               => "รายการจ่ายเงิน 100 บาท",
                        "TeamID"                => $teamid,
                        'CostBranch'            => $this->getPay(),
                        "LeadApproveStatus"     => $this->getApproveStatus($teamno, $depid, $fnno, $fnyear),
                        "data"                  => $team,
                        "cause"                 => $this->getReason()
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"        => "FAILED",
                        "message"       => "ไม่มีรายการจ่ายเงิน",
                    ), 200
                );
            }
        }
    }

    public function checkCreateMaster($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT TOP 1 * FROM TSR_DB1.dbo.SaleTeam_Work100 WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getAmountPay($date, $citizen) {
        $sql = "exec Allowance.dbo.CostBranch_Allowance_100 ?, ?";
        $stmt = $this->db->query($sql, array($date, $citizen));
        if ($stmt->num_rows() > 0) {
            // print_r($stmt->row()->amount);
            return $stmt->row();
        } else {
            // print_r(0);
            return 0;
        }
    }

    public function getPay() {
        $date = date('d-m-Y');
        $sql = "SELECT CONVERT(varchar, LockDate, 105), IsActive FROM Allowance.dbo.CostBranch_LockData_100
                WHERE CONVERT(varchar, LockDate, 105) = ? AND IsActive = 1";
        $stmt = $this->db->query($sql, array($date));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getApproveStatus($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT LeadApproveStatus FROM TSR_DB1.dbo.SaleTeam_Work100 WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->LeadApproveStatus;
        } else {
            return 0;
        }
    }
}

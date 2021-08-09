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

    public function index_get() {
        $checkDate  = date('Y-m-d H:i:s', strtotime($this->input->get('checkdate')));
        $citizen    = $this->input->get('citizen');
        $this->getAmountPay($checkDate, $citizen);
    }

    public function index_post() {
        $teamno     = str_replace("-", "", $this->input->post('teamno'));
        $depid      = $this->input->post('depid');
        $empid      = $this->input->post('empid');
        // $fnyear     = $this->input->post('fnyear');
        // $fnno       = $this->input->post('fnno');
        $fnno       = $this->getFnNo($depid);
        $fnyear     = $this->getFnYear($depid);
        $paydate    = date('Y-m-d H:i:s');

        $dontUse    = 1;

        if ($dontUse != 1) {

          if ($fnno != 0) {

              if ($fnyear != 0) {

                if (!$this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {

                      $sql = "SELECT SL.saleemp, SL.salecode, SL.FName + ' ' + SL.LName as Fullname, SM.CitizenID
                              FROM TSR_Application.dbo.NPT_Sale_Log AS SL WITH(NOLOCK)
                              INNER JOIN TSR_Application.dbo.NPT_Sale_Hierarchy AS SH on SL.SaleCode = SH.CSaleCode AND SL.FnNo = SH.FnNo AND SL.FnYear = SH.FnYear AND SL.DepID = SH.DepID
                              LEFT JOIN TSR_Application.dbo.NPT_Sale_Main AS SM ON SL.SaleID = SM.SaleID
                              WHERE ISNULL(LEFT(SL.Salecode,4), '-')+ISNULL(cast(SL.TeamNo AS varchar(4)),'-') = ?
                              AND SL.PosID < 3 AND SL.TeamNo IS NOT NULL AND SL.SaleStatus != 'R' AND SL.DepID = ?
                              AND SL.FnYear = ? AND SL.FnNo = ? AND SL.PositID NOT IN ('65','85')
                              ORDER BY saleemp"; //AND ISNULL(SL.SaleEmpType1, 0) != 8";
                      $stmt = $this->db->query($sql, array($teamno, $depid, $fnyear, $fnno));

                      if ($stmt->num_rows() > 0) {
                          $teamlist = $stmt->result_array();
                          $team = [];
                          foreach($teamlist as $k => $v) {
                            $result = $this->getAmountPay($paydate, $v["CitizenID"]);
                            if ($result) {
                                if ($result->amount > 0) {
                                    $t = array(
                                        "detailId"          => 0,
                                        "saleemp"           => $v["saleemp"],
                                        "salecode"          => $v["salecode"],
                                        "Fullname"          => $v["Fullname"],
                                        "CitizenID"         => $v["CitizenID"],
                                        "FnYear"            => $result->FnYear,
                                        "FnNo"              => $result->FnNo,
                                        "LeadApproveStatus" => 0,
                                        "LeadCheckTime"     => NULL,
                                        "PaymentStatus"     => 0,
                                        "PaymentAmount"     => $result->amount,
                                        "PaymentBalance"    => '0',
                                        "SwitchStatus"      => 0,
                                        "CauseStatus"       => 0
                                    );

                                    array_push($team, $t);
                                 }
                            }
                        }

                          if (count($team) > 0) {
                              $this->response(
                                  array(
                                      "status"                => "SUCCESS",
                                      "message"               => "รายการจ่ายเบี้ยเลี้ยง 100",
                                      "TeamID"                => 0,
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
                                      "message"       => "ไม่มีรายการจ่ายเบี้ยเลี้ยง 100",
                                  ), 200
                              );
                          }
                      } else {
                          $this->response(
                              array(
                                  "status"    => "FAILED",
                                  "message"   => "ไม่พบข้อมูลสายงาน",
                                  "data"      => ""
                              ), 200
                          );
                      }
                  } else {
                    $teamid = $this->getId($teamno, $depid, $fnno, $fnyear);
                    $sql = "SELECT * FROM TSR_DB1.dbo.SaleTeam_Work100_Detail WITH(NOLOCK)
                            WHERE TeamID = ?
                            AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY DetailID";
                    $stmt = $this->db->query($sql, array($teamid));
                    $teamlist = $stmt->result_array();
                    $team = [];
                    foreach($teamlist as $k => $v) {
                        $result = $this->getAmountPay($paydate, $v["CitizenID"]);
                        if ($result) {
                            if ($result->amount > 0) {
                                  $t = array(
                                      "detailId"          => $v["DetailID"],
                                      "saleemp"           => $v["EmpID"],
                                      "salecode"          => $v["SaleCode"],
                                      "Fullname"          => $v["EmpName"],
                                      "CitizenID"         => $v["CitizenID"],
                                      "FnNo"              => $v["FnNo"],
                                      "FnYear"            => $v["FnYear"],
                                      "LeadApproveStatus" => $v["LeadApproveStatus"],
                                      "LeadCheckTime"     => $v["LeadCheckTime"],
                                      "PaymentStatus"     => $v["PaymentStatus"],
                                      "PaymentAmount"     => $v["PaymentAmount"],
                                      "PaymentBalance"    => $v["PaymentBalance"],
                                      "SwitchStatus"      => $v["LeadApproveStatus"],
                                      "CauseStatus"       => is_null($v["LeadCause"]) ? 0 : $v["LeadCause"]
                                  );

                                  array_push($team, $t);
                            }
                        }
                    }

                    if (count($team) > 0) {
                        $this->response(
                            array(
                                "status"                => "SUCCESS",
                                "message"               => "รายการจ่ายเบี้ยเลี้ยง 100",
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
                                "message"       => "ไม่มีรายการจ่ายเบี้ยเลี้ยง 100",
                            ), 200
                        );
                    }
                  }
              } else {
                  $this->response(
                      array(
                          "status"    => "FAILED",
                          "message"   => "ไม่พบข้อมูลปีของปักษ์ ณ วันที่ปัจจุบัน",
                          "data"      => ""
                      ), 200
                  );
              }
          } else {
              $this->response(
                  array(
                      "status"    => "FAILED",
                      "message"   => "ปิดปักษ์แล้ว ณ วันที่ปัจจุบัน",
                      "data"      => ""
                  ), 200
              );
          }
      } else {
          $this->response(
              array(
                  "status"    => "FAILED",
                  "message"   => "เมนูนี้ยังไม่พร้อมใช้งาน",
                  "data"      => ""
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

    public function getFnNo($depid) {
        $sql = "SELECT TOP 1 Fortnight_no FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ?
                AND (CAST(DATEADD(YEAR, 543, GETDATE()) AS DATE) BETWEEN OpenDate AND CloseDate) ORDER BY Fortnight_no DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->Fortnight_no;
        } else {
            return 0;
        }
    }

    public function getFnYear($depid) {
        $sql = "SELECT TOP 1 Fortnight_year FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ?
                AND (CAST(DATEADD(YEAR, 543, GETDATE()) AS DATE) BETWEEN OpenDate AND CloseDate) ORDER BY Fortnight_year DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->Fortnight_year;
        } else {
            return 0;
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

    public function getId($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT TOP 1 * FROM TSR_DB1.dbo.SaleTeam_Work100 WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->TeamID;
        } else {
            return 0;
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

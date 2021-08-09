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
        // $fnno   = $this->input->post('fnno');
        // $fnyear = $this->input->post('fnyear');
        $empId  = $this->input->post('empId');
        $fnno   = $this->getFnNo($depid);
        $fnyear = $this->getFnYear($depid);
        $CloseFnNo  = $this->getCloseFnNo($depid);
        $CloseNum   = $this->getCloseNum($depid);

        if ($fnno != 0) {

            if ($fnyear != 0) {

              if (!$this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {

                  $sql = "SELECT SL.saleemp, SL.salecode, SL.FName + ' ' + SL.LName as Fullname, SM.CitizenID
                          FROM TSR_Application.dbo.NPT_Sale_Log AS SL WITH(NOLOCK)
                          INNER JOIN TSR_Application.dbo.NPT_Sale_Hierarchy AS SH on SL.SaleCode = SH.CSaleCode AND SL.FnNo = SH.FnNo AND SL.FnYear = SH.FnYear AND SL.DepID = SH.DepID AND SL.PosID = SH.PosID
                          LEFT JOIN TSR_Application.dbo.NPT_Sale_Main AS SM ON SL.SaleID = SM.SaleID
                          WHERE ISNULL(LEFT(SL.Salecode,4), '-')+ISNULL(cast(SL.TeamNo AS varchar(4)),'-') = ?
                          AND SL.PosID < 3 AND SL.TeamNo IS NOT NULL AND SL.SaleStatus != 'R' AND SL.DepID = ?
                          AND SL.FnYear = ? AND SL.FnNo = ? AND SL.PositID NOT IN ('65','85')
                          ORDER BY saleemp"; //AND ISNULL(SL.SaleEmpType1, 0) != 8";
                  $stmt = $this->db->query($sql, array($teamno, $depid, $fnyear, $fnno));

                    if ($stmt->num_rows() > 0) {
                        $result = $stmt->result_array();
                        $team = [];
                        foreach($result as $k => $v) {
                          $t = array(
                              "teamId"            => 0,
                              "detailId"          => 0,
                              "saleemp"           => $v["saleemp"],
                              "salecode"          => $v["salecode"],
                              "Fullname"          => $v["Fullname"],
                              "CitizenID"         => $v["CitizenID"],
                              "PayAmount"         => '200',
                              "CheckTime"         => '0',
                              "LeadApproveStatus" => 0,
                              "LeadCheckTime"     => NULL,
                              "saletype"          => $v["salecode"] == NULL ? 0 : $this->getSaleType($depid, $fnno, $fnyear, $v["salecode"]),
                              "CostBranch"        => $this->getCostBranch(),
                              "CloseFnNo"         => $CloseFnNo,
                              "CloseNum"          => $CloseNum,
                              "SaleImage"         => "",
                              "EmpImage"          => $v["saleemp"] == NULL ? "" : str_replace('A','0',$v["saleemp"]),
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
                              "message"   => "ไม่พบข้อมูลสายงาน",
                              "data"      => ""
                          ), 200
                      );
                    }
                } else {
                    $Teamid = $this->getId($teamno, $depid, $fnno, $fnyear);
                    $sql = "SELECT * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail WITH(NOLOCK)
                            WHERE TeamID = ?
                            AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY DetailID";
                    $stmt = $this->db->query($sql, array($Teamid));

                    if ($stmt->num_rows() > 0) {
                      $result = $stmt->result_array();
                      $team = [];
                      foreach($result as $k => $v) {
                            $t = array(
                                "teamId"            => $v["TeamID"],
                                "detailId"          => $v["DetailID"],
                                "saleemp"           => $v["EmpID"],
                                "salecode"          => $v["SaleCode"],
                                "Fullname"          => $v["EmpName"],
                                "CitizenID"         => $v["CitizenID"],
                                "PayAmount"         => $v["LeadApproveStatus"] == 0 ? '200' : strval($v["PaymentAmount"]),
                                "CheckTime"         => $v["LeadCheckTime"] == NULL ? '0' : '1',
                                "LeadApproveStatus" => $v["LeadApproveStatus"],
                                "LeadCheckTime"     => $v["LeadCheckTime"],
                                "saletype"          => $v["SaleCode"] == NULL ? 0 : $this->getSaleType($depid, $fnno, $fnyear, $v["SaleCode"]),
                                "CostBranch"        => $this->getCostBranch(),
                                "CloseFnNo"         => $CloseFnNo,
                                "CloseNum"          => $CloseNum,
                                "SaleImage"         => $v["Image"] == NULL ? "" : $v["Image"],
                                "EmpImage"          => $v["EmpID"] == NULL ? "" : str_replace('A','0',$v["EmpID"]),
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

    public function getCloseFnNo($depid) {
        $sql = "SELECT TOP 1 * FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ? AND CONVERT(varchar, FinishDate, 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY Fortnight_no DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getCloseNum($depid) {
        $sql = "SELECT TOP 1 DateOffset FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName
                WHERE DepID = ? AND CONVERT(varchar, FinishDate, 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY Fortnight_no DESC";
        $stmt = $this->db->query($sql, array($depid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->DateOffset;
        } else {
            return 0;
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

    public function getId($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT TeamID FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->TeamID;
        } else {
            return 0;
        }
    }

    public function getCostBranch() {
        $sql = "SELECT CONVERT(varchar, LockDate, 105) AS IsDate, IsActive FROM SQLUAT.Allowance.dbo.CostBranch_LockData
                WHERE CONVERT(varchar, LockDate, 105) = CONVERT(varchar, GETDATE(), 105) AND IsActive = 1";
        $stmt = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getSaleType($depid, $fnno, $fnyear, $saleno) {
        $sql = "SELECT TOP 1 CASE WHEN DATEDIFF(dd,DATEADD(dd,-1,StartDate),GETDATE()) >= 31 THEN 1 ELSE (CASE WHEN saleempType = 1 THEN 0 ELSE 1 END) END AS EmpType
                FROM TSR_Application.dbo.NPT_Sale_Log
                WHERE DepID = ? AND FnNo = ? AND FnYear = ? AND SaleCode = ?
                AND SaleStatus IN ('N','P','D') AND PositID NOT IN ('65','85') AND PosID < 3 ORDER BY CreateDate DESC";
        $stmt = $this->db->query($sql, array($depid, $fnno, $fnyear, $saleno));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->EmpType;
        } else {
            return 9;
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

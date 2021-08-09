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
        // $fnno           = $this->input->post('fnno');
        // $fnyear         = $this->input->post('fnyear');
        $lat            = $this->input->post('latitude');
        $lon            = $this->input->post('longitude');
        $team_list      = json_decode($this->input->post('teamlist'), true);
        $date           = date('Y-m-d H:i:s');
        $approve_status = 1;
        $fnno           = $this->getFnNo($depid);
        $fnyear         = $this->getFnYear($depid);

        if ($fnno != 0) {

            if ($fnyear != 0) {

                if (!$this->getCostBranch()) {

                    if (!$this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {
                        $data = array(
                          'TeamCode'              => $teamno,
                          'EmpID'                 => $empId,
                          'FnYear'                => $fnyear,
                          'FnNo'                  => $fnno,
                          'DepID'                 => $depid,
                          'LeadCheckTime'         => $date,
                          'LeadCheckNum'          => 0,
                          'LeadCheckWorkNum'      => 0,
                          'LeadCheckOutNum'       => 0,
                          'LeadApproveStatus'     => $approve_status,
                          'SupCheckNum'           => 0,
                          'SupCheckWorkNum'       => 0,
                          'SupCheckOutNum'        => 0,
                          'SupApproveStatus'      => 0,
                          'PaymentCheckNum'       => 0,
                          'PaymentCheckWorkNum'   => 0,
                          'PaymentCheckOutNum'    => 0,
                          'PaymentAmount'         => 0,
                          'PaymentBalance'        => 0,
                          'PaymentStatus'         => 0,
                          'CreateDate'            => $date,
                          'CreateBy'              => $empId
                        );

                        $stmt = $this->db->insert('TSR_DB1.dbo.SaleTeam_Work', $data);
                        if ($stmt) {
                            $id = $this->last_insert_id($teamno, $depid, $fnno, $fnyear);
                            foreach($team_list as $k => $v) {
                                $detail = array(
                                    'TeamID'                => $id,
                                    'EmpID'                 => $v['saleemp'],
                                    'EmpName'               => $v['Fullname'],
                                    'SaleCode'              => $v['salecode'],
                                    'LeadCheckTime'         => $v['CheckTime'] == '0' ? NULL : $date,
                                    'Latitude'              => $v['CheckTime'] == '0' ? NULL : $lat,
                                    'Longitude'             => $v['CheckTime'] == '0' ? NULL : $lon,
                                    'LeadApproveStatus'     => $v['LeadApproveStatus'] == '0' ? 0 : 1,
                                    'SupApproveStatus'      => 0,
                                    'PaymentStatus'         => 0,
                                    'PaymentAmount'         => $v['LeadApproveStatus'] == '0' ? 0 : $v['PayAmount'],
                                    'PaymentBalance'        => 0,
                                    'CitizenID'             => $v['CitizenID'],
                                    'CreateDate'            => $date,
                                    'CreateBy'              => $empId
                                );

                                $stmt2 = $this->db->insert('TSR_DB1.dbo.SaleTeam_Work_Detail', $detail);
                            }

                            if ($this->db->affected_rows() > 0) {
                                $sql    = "UPDATE TSR_DB1.dbo.SaleTeam_Work
                                           SET LeadCheckNum = (
                                                SELECT COUNT(sw.DetailID) AS total FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS sw
                                                WHERE sw.TeamID = '" . $id . "'
                                           ), LeadCheckWorkNum = (
                                                SELECT COUNT(sw.DetailID) AS approve FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS sw
                                                WHERE sw.TeamID = '" . $id . "' AND sw.LeadApproveStatus = 1
                                           ), LeadCheckOutNum = (
                                                SELECT COUNT(sw.DetailID) AS unapprove FROM TSR_DB1.dbo.SaleTeam_Work_Detail AS sw
                                                WHERE sw.TeamID = '" . $id . "' AND sw.LeadApproveStatus = 0
                                           )
                                           WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10),CreateDate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
                                 $stmt3   = $this->db->query($sql, array($id, $fnyear, $fnno, $depid));
                                   if ($stmt3) {
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
                        } else {
                          $this->response(
                              array(
                                  "status"    => "FAILED",
                                  "message"   => "ระบบไม่สามารถบันทึกข้อมูลได้",
                                  "data"      => ""
                              ), 200
                          );
                        }

                    } else {
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
                                         ), LeadCheckTime = ?, LeadApproveStatus = ?, UpdateDate = ?, UpdateBy = ?
                                         WHERE TeamID = ? AND FnYear = ? AND FnNo = ? AND DepID = ? AND CONVERT(VARCHAR(10),CreateDate,126) = CONVERT(VARCHAR(10),GETDATE(),126)";
                               $stmt   = $this->db->query($sql, array($date, $approve_status, $date, $empId, $teamid, $fnyear, $fnno, $depid));
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

                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกข้อมูลได้ เนื่องจากเกินเวลาที่กำหนด",
                            "data"      => ""
                        ), 200
                    );
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

        public function getCostBranch() {
            $sql = "SELECT CONVERT(varchar, LockDate, 105) AS IsDate, IsActive FROM Allowance.dbo.CostBranch_LockData
                    WHERE CONVERT(varchar, LockDate, 105) = CONVERT(varchar, GETDATE(), 105) AND IsActive = 1";
            $stmt = $this->db->query($sql);
            if ($stmt->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function checkCreateMaster($teamno, $depid, $fnno, $fnyear) {
            $sql = "SELECT TOP 1 * FROM TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                    AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
            $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
            if ($stmt->num_rows() > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function last_insert_id($teamno, $depid, $fnno, $fnyear) {
            $sql = "SELECT TOP 1 * FROM TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                    AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY TeamID DESC";
            $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
            return $stmt->row()->TeamID;
        }
}

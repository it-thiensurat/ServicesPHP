<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

class AddSale extends REST_Controller {
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->img_url = 'http://thiensurat.com/fileshare01';
    }

    public function index_get() {
        echo $this->config->config['base_url'];
    }

    public function index_post() {
        $teamno     = $this->input->post('teamNo');
        $newid      = $this->input->post('newId');
        $newname    = $this->input->post('newName');
        $empid      = $this->input->post('empId');
        $depid      = $this->input->post('depid');
        $date       = date('Y-m-d H:i:s');
        $checksaleid    = $this->checkSaleCitizenID($newid);
        $checkcitiid    = $this->checkNewCitizenID($newid);
        $fnno           = $this->getFnNo($depid);
        $fnyear         = $this->getFnYear($depid);

        if (!$checksaleid) {
            if ($checkcitiid == 0) {
                if ($fnno != 0) {
                    if ($fnyear != 0) {
                        if ($this->checkCreateMaster($teamno, $depid, $fnno, $fnyear)) {
                            $id = $this->last_insert_id($teamno, $depid, $fnno, $fnyear);
                            $data       = array(
                                'TeamID'                => $id,
                                'EmpName'               => $newname,
                                'LeadApproveStatus'     => 0,
                                'SupApproveStatus'      => 0,
                                'PaymentStatus'         => 0,
                                'PaymentAmount'         => 0,
                                'PaymentBalance'        => 0,
                                'CitizenID'             => $newid,
                                'CreateDate'            => $date,
                                'CreateBy'              => $empid,
                            );

                            $stmt = $this->db->insert('SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail', $data);
                                if ($stmt) {
                                        $this->response(
                                            array(
                                                'status'    => 'SUCCESS'
                                                , 'message' => 'บันทึกข้อมูลเรียบร้อย'
                                                , 'data'    => '',
                                            ), 200
                                        );
                                } else {
                                    $this->db->trans_rollback();
                                    $this->response(
                                        array(
                                            'status'    => 'FAILED'
                                            , 'message' => 'บันทึกข้อมูลไม่สำเร็จ!!'
                                            , 'data'    => $this->db->error(),
                                        ), 404
                                    );
                                }
                            } else {
                              $this->response(
                                  array(
                                      "status"    => "FAILED",
                                      "message"   => "ไม่พบข้อมูลทีมขาย!!",
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
            } else {
              $this->response(
                  array(
                      "status"    => "FAILED",
                      "message"   => "หมายเลขบัตรประชาชนนี้มีบันทึกในระบบแล้ว",
                      "data"      => ""
                  ), 200
              );
            }
        } else {
          $this->response(
              array(
                  "status"    => "FAILED",
                  "message"   => "หมายเลขบัตรประชาชนนี้มีในโครงสร้างฝ่ายขายแล้ว",
                  "data"      => ""
              ), 200
          );
        }
    }

    public function checkSaleCitizenID($newid) {
        $sql = "SELECT TOP 1 *
                FROM TSR_Application.dbo.NPT_Sale_Main AS SM WITH(NOLOCK)
                INNER JOIN TSR_Application.dbo.NPT_Sale_Log AS SL ON SL.SaleID = SM.SaleID AND SL.LogID = (SELECT MAX(SS.LogID)
                          FROM TSR_Application.dbo.NPT_Sale_Log AS SS
								          WHERE SS.SaleID = SL.SaleID
                          AND SL.SaleStatus != 'R')
                WHERE SM.CitizenID = ? ";
        $stmt = $this->db->query($sql, array($newid));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkNewCitizenID($newid) {
        $sql = "SELECT TOP 1 TeamID
                FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail WITH(NOLOCK)
                WHERE CitizenID = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105)
                ORDER BY TeamID DESC";
        $stmt = $this->db->query($sql, array($newid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->TeamID;
        } else {
            return 0;
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
        $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY CreateDate DESC";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function last_insert_id($teamno, $depid, $fnno, $fnyear) {
        $sql = "SELECT TOP 1 * FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work WHERE TeamCode = ? AND DepID = ? AND FnNo = ? AND FnYear = ?
                AND CONVERT(varchar, CreateDate , 105) = CONVERT(varchar, GETDATE(), 105) ORDER BY TeamID DESC";
        $stmt = $this->db->query($sql, array($teamno, $depid, $fnno, $fnyear));
        return $stmt->row()->TeamID;
    }
}

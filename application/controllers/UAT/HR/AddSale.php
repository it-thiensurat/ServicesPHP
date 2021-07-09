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
        $teamid     = $this->input->post('teamId');
        $newid      = $this->input->post('newId');
        $newname    = $this->input->post('newName');
        $empid      = $this->input->post('empId');
        $date       = date('Y-m-d H:i:s');
        $checksaleid    = $this->checkSaleCitizenID($newid);
        $checkcitiid    = $this->checkNewCitizenID($newid);
        $data       = array(
            'TeamID'                => $teamid,
            'EmpName'               => $newname,
            'CitizenID'             => $newid,
            'LeadApproveStatus'     => 0,
            'SupApproveStatus'      => 0,
            'PaymentAmount'         => 0,
            'PaymentStatus'         => 0,
            'CreateDate'            => $date,
            'CreateBy'              => $empid,
        );

        if ($checksaleid != 'N') {
            if ($checkcitiid == 0) {

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
        $sql = "SELECT TOP 1 SL.SaleStatus
                FROM SQLUAT.TSR_Application.dbo.NPT_Sale_Main AS SM WITH(NOLOCK)
                LEFT JOIN SQLUAT.TSR_Application.dbo.NPT_Sale_Log AS SL ON SL.SaleID = SM.SaleID AND SL.LogID = (SELECT MAX(SS.LogID)
                          FROM SQLUAT.TSR_Application.dbo.NPT_Sale_Log AS SS
								          WHERE SS.SaleID = SL.SaleID)
                WHERE SM.CitizenID = ? ";
        $stmt = $this->db->query($sql, array($newid));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->SaleStatus;
        } else {
            return 'Z';
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
}

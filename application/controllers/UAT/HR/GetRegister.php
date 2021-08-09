<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');
class GetRegister extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->jwt = new CreatorJwt();
        $this->apiKey = $this->config->item('key_token');
        $this->authorization = '';
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
                if ($this->ApiModel->checkApiKey($apiKey)) {
                    return;
                } else {
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
        /**
         * Decode token
         */
        $data       = $this->jwt->DecodeToken($this->authorization, $this->apiKey);
        // print_r($data);
        // exit();
        $empId      = $data['empId'];
        /**
         * End
         */

        $year  = $this->input->post('y');
        $month = $this->input->post('m');

        $dateReport = '';
        if ($year == '' || $month == '') {
            $dateReport = date('Ym');
        } else {
            $dateReport = $year . $month;
        }

        $sql   = "SELECT rm.RecruitmentId, rm.Title, rm.Firstname, rm.Lastname, rm.CitizenId, rm.BirthDate, CASE WHEN rm.Gender = '1' THEN 'M' ELSE 'F' END AS Gender,
                    rm.Mobile, rm.Picture, rm.LocationName, rm.Province, rm.District, rm.SubDistrict, rm.Postcode, rm.CompanyCode, cm.CompanyTh, rm.PositionName, rm.RecruitmentDate
                    FROM SQLUAT.TSR_DB1.dbo.RECRUITMENT_MASTER AS rm
                    LEFT JOIN SQLUAT.TSR_DB1.dbo.COMPANY_MASTER AS cm ON rm.CompanyCode = cm.CompanyCode
                    WHERE rm.AdviserId = ? AND LEFT(CONVERT(varchar, rm.RecruitmentDate,112),6) = ?";
        $stmt   = $this->db->query($sql, array($empId, $dateReport));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get register successfull.",
                    "count"     => count($result),
                    "all"       => $this->getAll($empId),
                    "data"      => $result,
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลผู้สมัคร",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function getAll($empId) {
        $sql    = "SELECT COUNT(rm.RecruitmentId) AS num FROM SQLUAT.TSR_DB1.dbo.RECRUITMENT_MASTER AS rm WHERE rm.AdviserId = ?";
        $stmt   = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->num;
        } else {
            return 0;
        }
    }
}

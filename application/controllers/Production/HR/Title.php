<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class Title extends REST_Controller { 
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

    public function index_get() {
        $sql    = "SELECT CompCode, Id, NameTh FROM [TSR-HRFOCUS-PRD].StarSoft.HR.EmployeePrefix WHERE CompCode = 'TSR'";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get title successfull.",
                    "data"      => $result,
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบข้อมูลคำนำหน้าชื่อ",
                    "token"     => "",
                    "data"      => ""
                ), 200
            );
        }
    }
}
?>
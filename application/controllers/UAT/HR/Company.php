<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class Company extends REST_Controller { 
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
        $sql = "SELECT CompanyId, CompanyCode, CompanyTh FROM [TSR_DB1].dbo.COMPANY_MASTER WHERE Status != 0";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get company successfull.",
                    "data"      => $result,
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่พบข้อมูลหน่วยงาน",
                    "data"      => ""
                ), 200
            );
        }
    }
}
?>
<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');
class GetPeriod extends REST_Controller {
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

        $sql1    = "SELECT YEAR(rm.RecruitmentDate) as y FROM SQLUAT.TSR_DB1.dbo.[RECRUITMENT_MASTER] AS rm
                    WHERE rm.AdviserId = ? GROUP BY YEAR(rm.RecruitmentDate) ORDER BY YEAR(rm.RecruitmentDate) DESC";
        $stmt1   = $this->db->query($sql1, array($empId));
        if ($stmt1->num_rows() > 0) {
            $year   = $stmt1->result_array();
            $data = [];
            foreach($year as $key => $value) {
                $res = array(
                    'y'     => $value['y'] . ""
                );

                array_push($data, $res);
            }

            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get period successfull.",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูล",
                    "data"      => ""
                ), 200
            );
        }
    }
}

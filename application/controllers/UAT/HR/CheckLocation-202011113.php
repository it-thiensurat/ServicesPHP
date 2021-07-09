<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');
class CheckLocation extends REST_Controller { 
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->jwt = new CreatorJwt();
        try {
            $author = false;
            $token = $this->input->request_headers();
            $apiKey = '';
            $authorization = '';
            foreach($token as $key => $value) {
                if ($key == 'Authorization') {
                    $authorization = $value;
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
        $dist           = $this->input->post('distance');
        $latitude       = $this->input->post('latitude');
        $longitude      = $this->input->post('longitude');
        $destlatitude   = $this->input->post('destlatitude');
        $destlongitude  = $this->input->post('destlongitude');


        $sql = "SELECT 6372.797 * (2 * ASIN ( 
                    SQRT (
                        POWER(SIN((" . $latitude . " - " . $destlatitude . ") * pi()/180 / 2), 2) + COS(" . $latitude . " * pi()/180) * COS(" . $destlatitude . " * pi()/180) 
                        * 
                        POWER(SIN((" . $longitude . " - " . $destlongitude . ") * pi()/180 / 2), 2) 
                    ) 
                )) as distance";
        $stmt = $this->db->query($sql);
        $km = $stmt->row()->distance;

        if ($dist > 0) {
            if ($km > $dist) {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "คุณไม่ได้อยู่ในระยะที่สามารถเช็คอินได้",
                        "data"      => true
                    ), 200
                );
            }  else {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "Check location successfull.",
                        "data"      => false
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Check location successfull.",
                    "data"      => false
                ), 200
            );
        }
    }
}
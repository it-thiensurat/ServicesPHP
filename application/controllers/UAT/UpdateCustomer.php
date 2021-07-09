<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class UpdateCustomer extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $contno         = $this->get('contno');
        $mobile         = $this->get('mobile');
        $token          = $this->get('token');
        // $picture        = $this->get('picture');

        if (isset($_FILES['picture']['tmp_name'])) {

            $file_id = $contno . '_' . $mobile;
            $ftp = ftp_connect('ftp.thiensurat.com');
            if ($ftp) {
                $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                if ($f) {
                    ftp_pasv($ftp, true);
                    $num_files = count($_FILES['taladImage']['tmp_name']);
                    for($i = 0; $i < $num_files; $i++) {
                        $image_dest = "/Smartcare/Customer_profile/" . $file_id . ".jpg";
                        $image = $_FILES['picture']['tmp_name'][$i];
                        $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                        array_push($upload, $fupload);
                    }

                    foreach($upload as $value) {
                        if ($value) {
                            array_push($success, $value);
                        }
                    }

                    if ($num_files == count($success)) {
                        $protocol = $this->url();
                        $picture = $protocol . $image_dest;
                        $this->updatePictureProfile($contno, $mobile, $picture);
                        // $this->response(
                        //     array(
                        //         'status' 	=> 'SUCCESS'
                        //         ,'message' 	=> 'บันทึกสำเร็จ!'
                        //         ,'data' 	=> '',
                        //     ), 200
                        // );
                    } else {
                        $this->response(
                            array(
                                'status' 	=> 'FAILED'
                                ,'message' 	=> 'พบข้อผิดพลาดในขั้นตอนการอัพโหลดรูปภาพ'
                                ,'data' 	=> null
                            ), REST_Controller::HTTP_ERROR
                        );
                    }
                } else {
                    $this->response(
                        array(
                            'status' 	=> 'FAILED'
                            ,'message' 	=> 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                            ,'data' 	=> null
                        ), REST_Controller::HTTP_ERROR
                    );
                }
            } else {
                $this->response(
                    array(
                        'status' 	=> 'FAILED'
                        ,'message' 	=> 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                        ,'data' 	=> null
                    ), REST_Controller::HTTP_ERROR
                );
            }
        } else {
            $this->updateToken($contno, $mobile, $token);
        }
    }

    public function updateToken($contno, $mobile, $token) {

        $sql = "UPDATE [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare SET customer_token = ? WHERE customer_contno = ? AND customer_mobile = ?";
        $stmt = $this->db->query($sql, array($token, $contno, $mobile));
        if ($stmt) {
            $this->response(
                array(
                    'status' 	=> 'SUCCESS', 
                    'message' 	=> 'อัพเดทโทเค่น', 
                    'data' 	    => ''
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED', 
                    'message' 	=> 'พบข้อผิดพลาด ไม่สามารถอัพเดทโทเค่นได้', 
                    'data' 	    => ''
                ), REST_Controller::HTTP_OK
            );
        }
    }

    public function updatePictureProfile($contno, $mobile, $picture) {
        $sql = "UPDATE [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare SET customer_picture = ? WHERE customer_contno = ? AND customer_mobile = ?";
        $stmt = $this->db->query($sql, array($picture, $contno, $mobile));
        if ($stmt) {
            $this->response(
                array(
                    'status' 	=> 'SUCCESS', 
                    'message' 	=> 'อัพเดทรูปโปรไฟล์สำเร็จ', 
                    'data' 	    => ''
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED', 
                    'message' 	=> 'พบข้อผิดพลาด ไม่สามารถอัพเดทรูปโปรไฟล์ได้', 
                    'data' 	    => ''
                ), REST_Controller::HTTP_OK
            );
        }
    }

    public function url(){
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    public function index_post() {
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
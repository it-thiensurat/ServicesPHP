<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Checker extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        
        $this->path_img = 'http://thiensurat.com/fileshare01';
	}
    public function index_get() {
        echo 'Test';
        exit();
    }
 
    public function index_post(){
        $taladId        = trim($this->input->post("taladId"));
        $rating         = trim($this->input->post("rating"));     
        $taladDetail    = trim($this->input->post("taladDetail"));
        $empid          = trim(preg_replace('/[^a-zA-Z0-9_ -]/s', '', strtoupper($this->input->post("empid"))));

        try {
            $this->db->trans_begin();

            $sql = "INSERT INTO [TSR_DB1].dbo.TSR_TALADNUD_RATE (tln_id, emp_id, detail, rate) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->query($sql, array($taladId, $empid, $taladDetail, $rating));

            $upload = array();
            $success = array();
            if ($stmt) {
                if (isset($_FILES['taladImage']['tmp_name'])) {
                    $last_id = $this->db->insert_id();
                    $file_path = "/TSRTaladSurvey/checkin/" . $last_id . "_";
                    $ftp = ftp_connect('ftp.thiensurat.com');
                    if ($ftp) {
                        $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                        if ($f) {
                            ftp_pasv($ftp, true);
                            $num_files = count($_FILES['taladImage']['tmp_name']);
                            $sql = "INSERT INTO [TSR_DB1].dbo.TSR_TALADNUD_IMAGES (check_id, item, url) VALUES (?, ?, ?)";
                            for($i = 0; $i < $num_files; $i++) {
                                $image_dest = "/TSRTaladSurvey/checkin/" . $last_id . "_" . $i . ".jpg";
                                $image = $_FILES['taladImage']['tmp_name'][$i];
                                $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                                $stmt = $this->db->query($sql, array($last_id, $i, $this->path_img . $image_dest));
                                if ($stmt) {
                                    array_push($upload, $fupload);
                                }
                            }

                            foreach($upload as $value) {
                                if ($value) {
                                    array_push($success, $value);
                                }
                            }

                            if ($num_files == count($success)) {
                                $this->db->trans_commit();
                                $this->response(
                                    array(
                                        'status' 	=> 'SUCCESS'
                                        ,'message' 	=> 'บันทึกสำเร็จ!'
                                        ,'data' 	=> '',
                                    ), 200
                                );
                            } else {
                                $this->db->trans_rollback();
                                $this->response(
                                    array(
                                        'status' 	=> 'FAILED'
                                        ,'message' 	=> 'พบข้อผิดพลาดในขั้นตอนการอัพโหลดรูปภาพ'
                                        ,'data' 	=> null
                                    ), REST_Controller::HTTP_ERROR
                                );
                            }
                        } else {
                            $this->db->trans_rollback();
                            $this->response(
                                array(
                                    'status' 	=> 'FAILED'
                                    ,'message' 	=> 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                                    ,'data' 	=> null
                                ), REST_Controller::HTTP_ERROR
                            );
                        }
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                'status' 	=> 'FAILED'
                                ,'message' 	=> 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
                                ,'data' 	=> null
                            ), REST_Controller::HTTP_ERROR
                        );
                    }
                } else {
                    $this->db->trans_commit();
                        $this->response(
                            array(
                                'status' 	=> 'SUCCESS'
                                ,'message' 	=> 'บันทึกสำเร็จ!'
                                ,'data' 	=> '',
                            ), 200
                        );
                }
            } else {
                $this->db->trans_rollback();
                $this->response(
                    array(
                        'status' 	=> 'FAILED'
                        ,'message' 	=> 'บันทึกไม่สำเร็จ!'
                        ,'data' 	=> null
                    ), REST_Controller::HTTP_ERROR
                );
            }

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->response(
                array(
                    'status' 	=> 'FAILED'
                    ,'message' 	=> $e->getMessage()
                    ,'data' 	=> $insertReady,
                ), 200
            );
        }
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
        
    }
}
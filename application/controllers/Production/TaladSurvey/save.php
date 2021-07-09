<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Save extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
		$this->load->database();
	}
    public function index_get() {

    }
 
    public function index_post(){
        $taladName      = trim($this->input->post("taladName"));      
        $taladOpenDay1  = trim($this->input->post("taladOpenDay1"));          
        $taladOpenDay2  = trim($this->input->post("taladOpenDay2"));          
        $taladOpenDay3  = trim($this->input->post("taladOpenDay3"));          
        $taladOpenDay4  = trim($this->input->post("taladOpenDay4"));          
        $taladOpenDay5  = trim($this->input->post("taladOpenDay5"));          
        $taladOpenDay6  = trim($this->input->post("taladOpenDay6"));          
        $taladOpenDay7  = trim($this->input->post("taladOpenDay7"));          
        $taladOpenTime  = trim($this->input->post("taladOpenTime"));          
        $taladCloseTime = trim($this->input->post("taladCloseTime"));           
        $taladAmount    = trim($this->input->post("taladAmount"));        
        $taladPrice     = trim($this->input->post("taladPrice"));       
        $taladTel       = trim($this->input->post("taladTel"));     
        $taladDetail    = trim($this->input->post("taladDetail"));        
        $taladLat       = trim($this->input->post("taladLat"));            
        $taladLon       = trim($this->input->post("taladLon"));
        $empid          = trim(preg_replace('/[^a-zA-Z0-9_ -]/s', '', strtoupper($this->input->post("empid"))));
        $empname        = trim($this->input->post("empname"));

        try {
            $this->db->trans_begin();

            $sql = "INSERT INTO [TSR_DB1].dbo.TSR_TALADNUD_SURVEY (tln_name, tln_detail, tln_open_day_1, tln_open_day_2, tln_open_day_3, tln_open_day_4, tln_open_day_5, tln_open_day_6, 
            tln_open_day_7, tln_open_time, tln_close_time, tln_amount, tln_telno, tln_price, tln_lat, tln_long, tln_empid, tln_fullname) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->query($sql, array(
                $taladName, $taladDetail, $taladOpenDay1, $taladOpenDay2, $taladOpenDay3, $taladOpenDay4, $taladOpenDay5, $taladOpenDay6,
                $taladOpenDay7, $taladOpenTime, $taladCloseTime, $taladAmount, $taladTel, $taladPrice, $taladLat, $taladLon, $empid, $empname
            ));

            $upload = array();
            $success = array();
            if ($stmt) {
                if (isset($_FILES['taladImage']['tmp_name'])) {
                    $last_id = $this->db->insert_id();
                    $file_path = "/TSRTaladSurvey/" . $last_id . "_";
                    $ftp = ftp_connect('ftp.thiensurat.com');
                    if ($ftp) {
                        $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                        if ($f) {
                            ftp_pasv($ftp, true);
                            $num_files = count($_FILES['taladImage']['tmp_name']);
                            for($i = 0; $i < $num_files; $i++) {
                                $image_dest = "/TSRTaladSurvey/" . $last_id . "_" . $i . ".jpg";
                                $image = $_FILES['taladImage']['tmp_name'][$i];
                                $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                                array_push($upload, $fupload);
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
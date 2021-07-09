<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Helpdesk extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        $this->img_url = 'http://thiensurat.com/fileshare01';
    }
    
    public function index_get() {
        
    }

    public function index_post() {
        $contno         = $this->input->post('contno');
        $customer_name  = $this->input->post('customerName');
        $customer_phone = $this->input->post('customerPhone');
        $description    = $this->input->post('description');
        $channel        = "05";
        $workcode       = 21;
        $now            = date('Y-m-d H:i:s');
        $accidentDate   = date('Y-m-d');

        $sqlCheck   = "SELECT CONCAT(SUBSTRING(MAX(InformID), 0, 5), RIGHT('000000' + CONVERT(VARCHAR, CONVERT(INTEGER, SUBSTRING(MAX(InformID), 5, 6) + 1)), 6)) AS InformID  
                        FROM TSR_ONLINE_MARKETING.dbo.Problem_Inform_Master";
        $stmtCheck  = $this->db->query($sqlCheck);
        $running    = $stmtCheck->row()->InformID;

        $this->db->trans_begin();

        $sql        = "INSERT INTO TSR_ONLINE_MARKETING.dbo.Problem_Inform_Master (InformID, InfromDateTime, AccidentDate, Contno, CustomerName, CustomerPhone, WorkCode, DataChannel) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt       = $this->db->query($sql, array($running, $now, $accidentDate, $contno, $customer_name, $customer_phone, $workcode, $channel));
        
        $upload = array();
        $success = array();
        if ($this->db->affected_rows() > 0) {
            $sql    = "INSERT INTO TSR_ONLINE_MARKETING.dbo.Problem_Inform_Details (InformID, ProblemID, ProblemDetail) 
                        VALUES (?, ?, ?)";
            $stmt   = $this->db->query($sql, array($running, 0, $description));
            if ($this->db->affected_rows() > 0) {
                if (isset($_FILES['problem']['tmp_name'])) {
                    $file_path = "/Smartcare/Problem/" . $running . "_";
                    $ftp = ftp_connect('ftp.thiensurat.com');
                    if ($ftp) {
                        $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                        if ($f) {
                            ftp_pasv($ftp, true);
                            $num_files = count($_FILES['problem']['tmp_name']);
                            for($i = 0; $i < $num_files; $i++) {
                                $image_name = $running . "_" . $i;
                                $image_dest = "/Smartcare/Problem/" . $image_name . ".jpg";
                                $image = $_FILES['problem']['tmp_name'][$i];
                                $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                                $image_url = $this->img_url . $image_dest;

                                $sql    = "INSERT INTO TSR_ONLINE_MARKETING.dbo.Problem_Inform_Details_Images (InformID, ProblemID, ImageItem, ImageUrl, ImageName, ImageType) 
                                            VALUES (?, ?, ?, ?, ?, ?)";
                                $stmt   = $this->db->query($sql, array($running, 0, ($i + 1), $image_url, $image_name, 'jpg'));
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
                                        ,'message' 	=> "ทางบริษัทได้รับเรื่องของคุณไว้แล้ว\nขอบคุณที่ใช้บริการ"
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
                            "status"    => "SUCCESS",
                            "message"   => "ทางบริษัทได้รับเรื่องของคุณไว้แล้ว\nขอบคุณที่ใช้บริการ",
                            "data"      => ''
                        ), 200
                    );
                }
            } else {
                $this->db->trans_rollback();
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถดำเนินการแจ้งปัญหาได้\nกรุณาติดต่อ Call center 1210",
                        "data"      => ''
                    ), 200
                );
            }
        } else {
            $this->db->trans_rollback();
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถดำเนินการแจ้งปัญหาได้\nกรุณาติดต่อ Call center 1210",
                    "data"      => ''
                ), 200
            );
        }
    }
}
<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class CheckInOut extends REST_Controller { 

    public function __construct() {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");

        $this->load->database();
        $this->jwt = new CreatorJwt();
        $this->authorization = '';
        $this->apiKey = $this->config->item('key_token');
        $this->img_url = 'http://thiensurat.com/fileshare01';
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
                //     if ($authorization != '') {
                //         // $this->authenWithToken($authorization, $apiKey);
                //         return;
                //     } else {
                //         return;
                //     }
                // } else {
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
        $checkTime      = $this->input->post('checkTime');
        $latitude       = $this->input->post('latitude');
        $longitude      = $this->input->post('longitude');
        $type           = $this->input->post('type');
        $version        = $this->input->post('version');
        
        if ($version == "") {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถบันทึกเวลาได้\nเวอร์ชั่นแอพฯ ของคุณเก่าเกินไป\nกรุณอัพเดทแอพพลิเคชั่น",
                    "data"      => "oldversion"
                ), 200
            );
        }

        // if (isset($_FILES['empimg']['tmp_name'])) {
        //     $this->response(
        //         array(
        //             "status"    => "SUCCESS",
        //             "message"   => "FILE",
        //             "data"      => ""
        //         ), 200
        //     );
        // } else if ($this->input->post('empimg')) {
        //     $this->response(
        //         array(
        //             "status"    => "SUCCESS",
        //             "message"   => "BASE 64",
        //             "data"      => ""
        //         ), 200
        //     );
        // } else {
        //     $this->response(
        //         array(
        //             "status"    => "SUCCESS",
        //             "message"   => "ไม่มีรูปภาพ",
        //             "data"      => ""
        //         ), 200
        //     );
        // }

        // exit();

        /**
         * Decode token
         */
        $data = $this->jwt->DecodeToken($this->authorization, $this->apiKey);
        $empId = $data['empId'];
        $cardId = $data['cardid'];
        $divisionId = $data['divisionId'];
        /**
         * End
         */

        $userID         = $this->cleanLetters($divisionId) . $this->cleanLetters($empId);
        $verifycode     = 1;
        $sensorid       = 999;
        $workcode       = 0;
        $sn             = null;
        $userexfmt      = 0;
        $create_date    = date("Y-m-d H:i:s");
        $image_url      = "";
        $fupload        = "";

        $data = array(
            'USERID'        => $userID,
            'CHECKTIME'     => $checkTime,
            'CHECKTYPE'     => $type,
            'VERIFYCODE'    => $verifycode,
            'SENSORID'      => $sensorid,
            'WorkCode'      => $workcode,
            'SN'            => $sn,
            'UserExtFmt'    => $userexfmt,
            'CitizenId'     => $cardId,
            'EmpId'         => $empId,
            'CreateDate'    => $create_date,
        );

        $result = $this->db->insert('SQLUAT.TSR_DB1.dbo.HRM_CHECKINOUT', $data);
        if ($result) {
            if (isset($_FILES['empimg']['tmp_name'])) {
                $ftp = ftp_connect('ftp.thiensurat.com');
                if ($ftp) {
                    $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                    if ($f) {
                        ftp_pasv($ftp, true);
                        $num_files = count($_FILES['empimg']['tmp_name']);
                        for($i = 0; $i < $num_files; $i++) {
                            $image_name = $empId . "_" . $i;
                            $image_dest = "/HR/Checkin/" . $image_name . ".jpg";
                            $image = $_FILES['empimg']['tmp_name'][$i];
                            $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                            $image_url = $this->img_url . $image_dest;
                        }
                    }
                }

                if ($fupload) {
                    $data2 = array(
                        'EmpID'         => $empId,
                        'CHECKTIME'     => $checkTime,
                        'CHECKTYPE'     => $type,
                        'Latitude'      => $latitude,
                        'Longitude'     => $longitude,
                        'CreateDate'    => $create_date,
                        'Image'         => $image_url
                    );
    
                    $result = $this->db->insert('SQLUAT.TSR_DB1.dbo.HRM_CHECKINOUT_LOCATION', $data2);
                    if ($result) {
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกเวลางานเรียบร้อย",
                                "data"      => ""
                            ), 200
                        );
                    } else {
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกเวลาได้ กรุณาติดต่อ IT",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกรูปภาพได้ กรุณาติดต่อ IT",
                            "data"      => ""
                        ), 200
                    );
                }
            } else if ($this->input->post('empimg')) {
                $empimg = $this->input->post('empimg');
                $ftp = ftp_connect('ftp.thiensurat.com');
                if ($ftp) {
                    $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                    if ($f) {
                        ftp_pasv($ftp, true);
                        $image_name = $empId . "_0";
                        $image_dest = "/HR/Checkin/" . $image_name . ".jpg";
                        $image      = $this->base64_to_jpeg($empimg, 'tmp.jpg');
                        $fupload    = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                        $image_url  = $this->img_url . $image_dest;
                    }
                }

                if ($fupload) {
                    $data2 = array(
                        'EmpID'         => $empId,
                        'CHECKTIME'     => $checkTime,
                        'CHECKTYPE'     => $type,
                        'Latitude'      => $latitude,
                        'Longitude'     => $longitude,
                        'CreateDate'    => $create_date,
                        'Image'         => $image_url
                    );

                    $result = $this->db->insert('SQLUAT.TSR_DB1.dbo.HRM_CHECKINOUT_LOCATION', $data2);
                    if ($result) {
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกเวลางานเรียบร้อย",
                                "data"      => ""
                            ), 200
                        );
                    } else {
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกเวลาได้ กรุณาติดต่อ IT",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกรูปภาพได้ กรุณาติดต่อ IT",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $data2 = array(
                    'EmpID'         => $empId,
                    'CHECKTIME'     => $checkTime,
                    'CHECKTYPE'     => $type,
                    'Latitude'      => $latitude,
                    'Longitude'     => $longitude,
                    'CreateDate'    => $create_date
                );

                $result = $this->db->insert('SQLUAT.TSR_DB1.dbo.HRM_CHECKINOUT_LOCATION', $data2);
                if ($result) {
                    $this->response(
                        array(
                            "status"    => "SUCCESS",
                            "message"   => "บันทึกเวลางานเรียบร้อย",
                            "data"      => ""
                        ), 200
                    );
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกเวลาได้ กรุณาติดต่อ IT",
                            "data"      => ""
                        ), 200
                    );
                }
            }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่สามารถบันทึกเวลาได้ กรุณาติดต่อ IT",
                    "data"      => ""
                ), 200
            );
        }
    }

    /**
     * ตัดตัวอักษรออกจากตัวเลข
     */
    public function cleanLetters($str) {
        return preg_replace("/[^0-9]/", "", $str);
    }

    /**
     * End
     */

    /**
     * แปลง Base64 เป็นรูป
     */
    public function base64_to_jpeg( $base64_string, $output_file ) {
        $ifp = fopen( $output_file, "wb" ); 
        fwrite( $ifp, base64_decode( $base64_string) ); 
        fclose( $ifp ); 
        return( $output_file ); 
    }
    /**
     * End
     */
}
?>
<?php
header('Content-Type: application/json');
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class Register extends REST_Controller { 
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

    public function index_post() {
        /**
         * Decode token
         */
        $data       = $this->jwt->DecodeToken($this->authorization, $this->apiKey);
        // print_r($data);
        // exit();
        $empId      = $data['empId'];
        $empName    = $data['firstname'] . " " . $data['lastname'];
        /**
         * End
         */

        $image_base_url = "http://thiensurat.com/fileshare01";
        $image_dest = "/HR/Recruitment/";
        $this->db->trans_begin();

        $titleId            = $this->input->post('titleId');
        $titleName          = $this->input->post('titleName');
        $firstName          = $this->input->post('firstName');
        $lastName           = $this->input->post('lastName');
        $citizenId          = $this->input->post('citizenId');
        $birthDate          = $this->input->post('birthDate');
        $gender             = $this->input->post('gender');
        $mobile             = $this->input->post('mobile');
        $subDistrictId      = $this->input->post('subDistrictId');
        $subDistrictName    = $this->input->post('subDistrictName');
        $districtId         = $this->input->post('districtId');
        $districtName       = $this->input->post('districtName');
        $provinceId         = $this->input->post('provinceId');
        $provinceName       = $this->input->post('provinceName');
        $zipcode            = $this->input->post('zipcode');
        $companyId          = $this->input->post('companyId');
        $companyName        = $this->input->post('companyName');
        $positionId         = $this->input->post('positionId');
        $positionName       = $this->input->post('positionName');
        $ChannelId          = $this->input->post('ChannelId');
        $locationName       = $this->input->post('locationName');

        if (isset($_FILES['gallery']['tmp_name'])) {
            $ftp = ftp_connect('ftp.thiensurat.com');
            if ($ftp) {
                $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                if ($f) {
                    ftp_pasv($ftp, true);
                    $num_files = count($_FILES['gallery']['tmp_name']);
                    for($i = 0; $i < $num_files; $i++) {
                        $image_dest = $image_dest . $citizenId . ".jpg";
                        $image = $_FILES['gallery']['tmp_name'][$i];
                        $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                        // array_push($upload, $fupload);
                    }
                }
            }
        }

        $data = array(
            'TitleId'           => $titleId,
            'Title'             => $titleName,
            'Firstname'         => $firstName,
            'Lastname'          => $lastName,
            'CitizenId'         => $citizenId,
            'BirthDate'         => $birthDate,
            'Gender'            => $gender,
            'Mobile'            => $mobile,
            'Picture'           => $image_base_url . "" . $image_dest,
            'SubDistrictCode'   => $subDistrictId,
            'SubDistrict'       => $subDistrictName,
            'DistrictCode'      => $districtId,
            'District'          => $districtName,
            'ProvinceCode'      => $provinceId,
            'Province'          => $provinceName,
            'Postcode'          => $zipcode,
            'CompanyId'         => $companyId,
            'CompanyCode'       => $companyName,
            'PositionId'        => $positionId,
            'PositionName'      => $positionName,
            'AdviserId'         => $empId,
            'AdviserName'       => $empName,
            'ChannelId'         => $ChannelId,
            'LocationName'      => $locationName,
            'RecruitmentDate'   => date('Y-m-d'),
            'CreatedDate'       => date('Y-m-d H:i:s'),
            'CreatedBy'         => $empId
        );

        $stmt = $this->db->insert("TSR_DB1.dbo.RECRUITMENT_MASTER", $data);
        if ($stmt) {
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
                    ,'message' 	=> 'พบข้อผิดพลาดในขั้นตอนการบันทึกข้อมูล'
                    ,'data' 	=> null
                ), 200
            );
        }
    }

    public function index_get() {
        echo 'Register';
    }
}
?>
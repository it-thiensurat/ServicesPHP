<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class CheckIN extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // $this->load->library('ftp');
        $this->img_url = 'http://thiensurat.com/fileshare01';
    }

    public function index_get() {
        // $config['hostname']     = 'ftp.thiensurat.com';
        // $config['username']     = 'fileshare01@thiensurat.com';
        // $config['password']     = 'CX8Q2Z7wO';
        // $config['debug']        = TRUE;

        // $ftp = $this->ftp->connect($config);

        
        // echo phpinfo();
        // $ftp = ftp_connect('ftp.thiensurat.com') or die("Could not connect to");
        // $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
        // print_r($f);
    }

    public function index_post() {
        $empId      = $this->input->post('empId');
        $saleEmp    = $this->input->post('saleEmp');
        $detailId   = $this->input->post('detailId');
        $latitude   = $this->input->post('latitude');
        $longitude  = $this->input->post('longitude');
        $payamount  = $this->input->post('PayAmount');

        if (isset($_FILES['empimg']['tmp_name'])) {
            $ftp = ftp_connect('ftp.thiensurat.com');
            if ($ftp) {
                $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                if ($f) {
                    ftp_pasv($ftp, true);
                    $num_files = count($_FILES['empimg']['tmp_name']);
                    for($i = 0; $i < $num_files; $i++) {
                        $image_name = $saleEmp . "_" . date('YmdHis');
                        $image_dest = "/HR/SaleCheckIn/" . $image_name . ".jpg";
                        $image = $_FILES['empimg']['tmp_name'][$i];
                        $fupload = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                        $image_url = $this->img_url . $image_dest;
                    }
                }
            }

            if ($fupload) {
                $data = array(
                    'LeadCheckTime'         => date('Y-m-d H:i:s'),
                    'Latitude'              => $latitude,
                    'Longitude'             => $longitude,
                    'LeadApproveStatus'     => 1,
                    'PaymentAmount'         => $payamount,
                    'UpdateBy'              => $empId,
                    'UpdateDate'            => date('Y-m-d H:i:s'),
                    'Image'                 => $image_url
                );

                $this->db->where('DetailID', $detailId);
                $result = $this->db->update('TSR_DB1.dbo.SaleTeam_Work_Detail', $data);
                if ($result) {
                    $this->response(
                        array(
                            "status"    => "SUCCESS",
                            "message"   => "บันทึกเวลาพนักงานขายเรียบร้อย",
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
            $data = array(
                'Latitude'              => $latitude,
                'Longitude'             => $longitude,
                'LeadApproveStatus'     => 1,
                'UpdateBy'              => $empId,
                'UpdateDate'            => date('Y-m-d H:i:s'),
                'Image'                 => ''
            );

            $this->db->where('DetailID', $detailId);
            $result = $this->db->update('TSR_DB1.dbo.SaleTeam_Work_Detail', $data);
            if ($result) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกเวลาพนักงานขายเรียบร้อย",
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
    }
}
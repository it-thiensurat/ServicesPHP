<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AddContractImage extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        // $ftp = ftp_connect('appuat.thiensurat.co.th');
        // if ($ftp) {
        //     $f = ftp_login($ftp, 'bigheaduat', 'ftPdaTa1976');
        //     if ($f) {
        //         // ftp_pasv($ftp, true);
        //         echo "ล็อคอิน FTP สำเร็จ";
        //         echo "\n";
        //         print_r($f);
        //     } else {
        //         echo "ล็อคอิน FTP ไม่สำเร็จ";
        //         echo "\n";
        //         print_r($f);
        //     }
        // } else {
        //     echo "เชื่อมต่อ FTP ไม่สำเร็จ";
        //     echo "\n";
        //     print_r($ftp);
        // }
    }

    public function index_post() {
        $refno      = $this->input->post('refnoBody');
        $datenow    = date("Y-m-d H:i:s");
        if (isset($_FILES['files'])) {
            $num_files = count($_FILES['files']['name']);
            $sql = "INSERT INTO SQLUAT.Bighead_Mobile.dbo.ContractImage (ImageID, RefNo, ImageName, ImageTypeCode, SyncedDate, FullPath) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $name = "";
            $imageid = "";
            for($i = 0; $i < $num_files; $i++) {
                $index      = strpos($_FILES['files']['name'][$i], "_");
                $type       = substr($_FILES['files']['name'][$i], 0, $index);
                $name       = substr($_FILES['files']['name'][$i], ($index + 1));
                $imageid    = pathinfo($name, PATHINFO_FILENAME);

                $stmt = $this->db->query($sql, array($imageid, $refno, $name, $type, $datenow, ""));
            }

            $ftp = ftp_connect('appuat.thiensurat.co.th');
            if ($ftp) {
                $f = ftp_login($ftp, 'bigheaduat', 'ftPdaTa1976');
                if ($f) {
                    ftp_pasv($ftp, true);
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
            // $this->response(
            //     array(
            //         'status' 	=> 'SUCCESS'
            //         ,'message' 	=> "มีรูปภาพ"
            //         ,'data' 	=> $imageid,
            //     ), 200
            // );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED'
                    ,'message' 	=> "ไม่มีรูปภาพ"
                    ,'data' 	=> $this->input->post(),
                ), 200
            );
        }

    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {
    }
}
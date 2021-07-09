<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class UpdateLocation extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $lat        = $this->get('lat');
        $lon        = $this->get('lon');
        $contno     = $this->get('contno');
        $serial     = $this->get('serial');
        $deviceid   = $this->get('device');
        $devicename = $this->get('device_name');
        $platform   = $this->get('platform');
        $update_date = date('Y-m-d H:i:s');

        $sql = "INSERT INTO [TSR_ONLINE_MARKETING].dbo.Customer_Smartcare_Tracking 
        (tracking_contno, tracking_product_serial, tracking_latitude, tracking_longitude, tracking_device_id, tracking_platform, tracking_device_name)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->query($sql, array($contno, $serial, $lat, $lon, $deviceid, $platform, $devicename));

        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Tracking",
                    "data"      => $this->get()
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAILED'
                    ,'message' 	=> 'ไม่สามารถระบุตำแหน่งได้'
                    ,'data' 	=> array($contno, $serial, $lat, $lon, $deviceid, $platform, $update_date)
                ), REST_Controller::HTTP_ERROR
            );
        }
    }

    public function index_post() {
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
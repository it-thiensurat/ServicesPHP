<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
header("Access-Control-Allow-Origin: *");

class getMarket extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    function index_get() { 
        $latitude   = $this->input->get('latitude');
        $longitude  = $this->input->get('longitude');

        // $this->response(
        //     array(
        //         'status' 	=> 'SUCCESS',
        //         'message' 	=> '',
        //         'data' 	    => $this->input->get(),
        //     ), REST_Controller::HTTP_OK
        // );

        // exit();

        if ($latitude == "") {
            $latitude = 13.898221;
        }

        if ($longitude == "") {
            $longitude = 100.514015;
        }

        // echo strtoupper('adsfdfafdsfa');
        // exit();

        // Old --> 3956
        // New --> 6371
        $sql = "SELECT TTS.tln_id, TTS.tln_name, TTS.tln_detail, TTS.tln_open_time, TTS.tln_close_time, TTS.tln_telno, TTS.tln_lat, TTS.tln_long, 
                6371 * (2 * 
                    ASIN ( 
                        SQRT (
                            POWER(SIN(($latitude - TTS.tln_lat) * pi()/180 / 2), 2) + COS($latitude * pi()/180) * COS(TTS.tln_lat * pi()/180) 
                            * 
                            POWER(SIN(($longitude -TTS.tln_long) * pi()/180 / 2), 2) 
                        ) 
                    )
                ) AS distance, TTS.tln_open_day_1, TTS.tln_open_day_2, TTS.tln_open_day_3, TTS.tln_open_day_4, TTS.tln_open_day_5, TTS.tln_open_day_6, TTS.tln_open_day_7 
                FROM [TSR_DB1].dbo.TSR_TALADNUD_SURVEY AS TTS ";
                // WHERE NOT EXISTS( SELECT tln_id FROM [TSR_DB1].dbo.TSR_TALADNUD_RATE WHERE tln_id = TTS.tln_id ) ORDER BY distance ASC";
        $stmt = $this->db->query($sql);
        $result = $stmt->result_array();
        if (count($result) > 0) {
            $r = [];
            foreach($result as $k => $v) {
                $rr = array(
                    'id'            => trim($v['tln_id']),
                    'name'          => trim($v['tln_name']),
                    'detail'        => trim($v['tln_detail']),
                    'opentime'      => trim($v['tln_open_time']),
                    'closetime'     => trim($v['tln_close_time']),
                    'phone'         => trim($v['tln_telno']),
                    'latitude'      => trim($v['tln_lat']),
                    'longitude'     => trim($v['tln_long']),
                    'distance'      => number_format($v['distance'], 2),
                    'url'           => 'http://thiensurat.com/fileshare01/TSRTaladSurvey/' . trim($v['tln_id']) . '_0.jpg',
                    'monday'        => trim($v['tln_open_day_1']),
                    'tuesday'       => trim($v['tln_open_day_2']),
                    'wednesday'     => trim($v['tln_open_day_3']),
                    'thursday'      => trim($v['tln_open_day_4']),
                    'friday'        => trim($v['tln_open_day_5']),
                    'saturday'      => trim($v['tln_open_day_6']),
                    'sunday'        => trim($v['tln_open_day_7']),
                    'check_detail'  => $this->getCheckerDetail(trim($v['tln_id'])),
                    'check_rate'    => $this->getCheckerRate(trim($v['tln_id'])),
                    'check_gallery' => $this->getCheckerGallery(trim($v['tln_id'])),
                );
                array_push($r, $rr);
            }

            $this->response(
                array(
                    'status' 	=> 'SUCCESS',
                    'message' 	=> '',
                    'data' 	    => $r,
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAIL',
                    'message' 	=> 'ไม่สามารถดึงข้อมูลได้',
                    'data' 	    => '',
                ), REST_Controller::HTTP_OK
            );
        }
    }

    function toRad($deg) {
        return $deg * pi()/180;
    }

    function getHoliday($id) {
        $sql = "SELECT TTS.tln_open_day_1, TTS.tln_open_day_2, TTS.tln_open_day_3, TTS.tln_open_day_4, TTS.tln_open_day_5, 
                TTS.tln_open_day_6, TTS.tln_open_day_7
                FROM [TSR_DB1].dbo.TSR_TALADNUD_SURVEY AS TTS 
                WHERE tln_id = ?";
        $stmt = $this->db->query($sql, array($id));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $r = [];
            foreach($result as $k => $v) {
                $rr = array(
                    'monday'    => trim($v['tln_open_day_1']),
                    'tuesday'   => trim($v['tln_open_day_2']),
                    'wednesday' => trim($v['tln_open_day_3']),
                    'thursday'  => trim($v['tln_open_day_4']),
                    'friday'    => trim($v['tln_open_day_5']),
                    'saturday'  => trim($v['tln_open_day_6']),
                    'sunday'    => trim($v['tln_open_day_7']),
                );
                array_push($r, $rr);
            }
            return $r;
        } else {
            return null;
        }
    }

    public function getCheckerDetail($id) {
        $sql = "SELECT detail FROM [TSR_DB1].dbo.TSR_TALADNUD_RATE WHERE tln_id = ? ORDER BY id DESC";
        $stmt = $this->db->query($sql, array($id));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->detail;
        } else {
            return '';
        }
    }

    public function getCheckerRate($id) {
        $sql = "SELECT rate FROM [TSR_DB1].dbo.TSR_TALADNUD_RATE WHERE tln_id = ? ORDER BY id DESC";
        $stmt = $this->db->query($sql, array($id));
        if ($stmt->num_rows() > 0) {
            return $stmt->row()->rate;
        } else {
            return 0;
        }
    }

    public function getCheckerGallery($id) {
        $sql = "SELECT TI.id, TI.check_id, TI.item, TI.url FROM  [TSR_DB1].dbo.TSR_TALADNUD_RATE AS TR 
                LEFT JOIN  [TSR_DB1].dbo.TSR_TALADNUD_IMAGES AS TI ON TR.id = TI.check_id
                WHERE TR.tln_id = ? ORDER BY TR.id DESC";
        $stmt = $this->db->query($sql, array($id));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    function index_put()
    {
        $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        // create a new index and respond with a status/errors
    }
 
    function index_post()
    {
        $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);   
        // update an existing index and respond with a status/errors
    }
 
    function index_delete()
    {
        $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        // delete a index and respond with a status/errors
    }
}
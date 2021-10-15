<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Notification extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $sql = "exec TSR_Application.dbo.getStartDate100";
        $stmt = $this->db->query($sql);
        // print_r($stmt->row()->IsStartDate100);
        // exit();
        if ($stmt->row()->IsStartDate100 == 1) {
            $ntf = array(
                'title'         => 'แจ้งเตือนการจ่ายเงิน',
                'body'          => 'กำหนดจ่ายเงินช่วยเหลือค่าครองชีพ',
                'sound'         => 'default',
            );

            $payload_data = array(
                "title"     => "แจ้งเตือนการจ่ายเงิน",
                "message"   => "กำหนดจ่ายเงินช่วยเหลือค่าครองชีพ",
            );

            $SERVER_KEY     = 'AAAALXarfr0:APA91bHqyr8WIP4J3wSPomFtUPop4dOXp-t51BvDTehUU73eGZDvLz0tFonrE5zCiO6kqpiOOZHQkrQ5av7LeNULD3b3zPnPm3U29US85p8wY4LqVNJsC_Ln1eZ9vmCqPexMZDx4BF-1';
            $SEND_URL       = 'https://fcm.googleapis.com/fcm/send';
            $SENDER_ID      = '195264478909';
            
            $fields = array(
                'to'                => '/topics/LeadSup',
                'priority'          => 'high',
                'notification'      => $ntf,
                'data'              => $payload_data,
            );

            $headers = array(
                'Authorization: Bearer '.$SERVER_KEY,
                'Content-Type: application/json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $SEND_URL);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            
            if($result === FALSE) {
                die('Curl failed:'.curl_error($ch));
            }
            curl_close($ch);
        }
    }
}
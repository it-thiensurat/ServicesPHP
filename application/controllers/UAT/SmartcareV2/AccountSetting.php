<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AccountSetting extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // $this->db = $this->load->database('uat', TRUE);
    }
    
    public function index_get() {
        
    }

    public function index_post() {
        $customerId     = $this->input->post('customer_id');
        $username       = $this->input->post('username');
        $password       = $this->input->post('password');
        $type           = $this->input->post('type');
        $date           = date('Y-m-d H:i:s');

        if (!$this->checkUsernameExist($username)) {
            if ($type == "save") {
                $sql = "INSERT INTO TSR_DB1.dbo.CUSTOMER_SMARTCARE_ACCOUNT (CustomerId, Username, Password, Created_by) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->query($sql, array($customerId, $username, md5($password), $customerId));
            } else if ($type == "update") {
                $sql = "UPDATE TSR_DB1.dbo.CUSTOMER_SMARTCARE_ACCOUNT SET Username = ?, Password = ?, Updated_date = ? WHERE CustomerId = ?";
                $stmt = $this->db->query($sql, array($username, md5($password), $date, $customerId));
            }
            
            if ($stmt) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => $type == "save" ? "ตั้งค่าบัญชีของคุณแล้ว" : "แก้ไขข้อมูลบัญชีของคุณแล้ว",
                        "data"      => $this->input->post()
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => $type == "save" ? "ไม่สามารถบันทึกการตั้งค่าได้" : "ไม่สามารถอัพเดทข้อมูลบัญชีได้",
                        "data"      => ''
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => $username . " มีผู้อื่นใช้งานแล้ว",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function checkUsernameExist($username) {
        $sql = "SELECT * FROM TSR_DB1.dbo.CUSTOMER_SMARTCARE_ACCOUNT WHERE Username = ?";
        $stmt = $this->db->query($sql, array($username));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
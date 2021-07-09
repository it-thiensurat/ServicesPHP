<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

class Authen extends REST_Controller { 

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
    
    }

    public function index_post() {
        $username           = $this->input->post('username');
        $password           = $this->input->post('password');
        $programVersion     = $this->input->post('version');
        $programId          = 9;

        $sql    = "EXEC TSR_Application.dbo.Branch_Authen_Authen @UsrName = ?, @Psw = ?, @ProgramID = ?, @ProgramVersion = ?";
        $stmt   = $this->db->query($sql, array($username, base64_encode($password), $programId, $programVersion));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Authentication successfull.",
                    "data"      => $result
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Authentication failed.",
                    "data"      => ""
                ), 200
            );
        }
    }
}
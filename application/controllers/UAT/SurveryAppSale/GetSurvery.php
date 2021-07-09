<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetSurvey extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    public function index_get() {
        $this->response(
            array(
                "status"    => "SUCCESS",
                "message"   => "Survey",
                "data"      => array(
                    "marryStatus"   => $this->getCustomerStatus(),
                    "homeStatus"    => $this->getCustomerResidence(),
                    "homeTime"      => $this->getCustomerResidenceTime(),
                    "job"           => $this->getCustomerJob(),
                    "jobTime"       => $this->getCustomerJobTime(),
                    "salary"        => $this->getCustomerSalary()
                )
            ), 200
        );
    }

    public function index_post()
    {
    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {

    }

    public function getCustomerStatus() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreCusStatus";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerResidence() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreResidenceStatus";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerResidenceTime() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreResidenceTime";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerJob() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreJobName";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerJobTime() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreJobTime";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerSalary() {
        $sql    = "SELECT * FROM [TSRData_Source].dbo.TSSM_CreditScoreCusSalary";
        $stmt   = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }
}
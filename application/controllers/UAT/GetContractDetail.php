<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetContractDetail extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $refno = $this->get('refno');
        $sql = "SELECT DM.Refno, DM.IDCard, DM.IDCard_Status, DM.PrefixName, DM.CustomerName,
            DA.AddressDetail, DA.Province, DA.Amphur, DA.District, DA.Zipcode, DA.TelHome,
            DA.TelMobile, DA.TelOffice, DA.EMail
            FROM [TSR_Application].dbo.DebtorAnalyze_Master AS DM
            LEFT JOIN [TSR_Application].dbo.DebtorAnalyze_Address AS DA ON DM.Refno = DA.Refno
            WHERE DM.Refno = '" . $refno . "' AND DA.AddressTypeCode = 'AddressInstall' ";
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status" => "SUCCESS",
                "message" => "Detail",
                "data" => $stmt->result_array()
            ), 200
        );
    }

    public function index_post() {
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
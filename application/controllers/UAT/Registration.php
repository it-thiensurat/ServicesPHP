<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class Registration extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
		$this->load->database();
	}
    public function index_get() {
        $sql = "SELECT TOP 1 DM.Refno, DM.CONTNO, DM.ProductSerial, DM.IDCard, DM.IDCard_Status, DM.PrefixName, DM.CustomerName,
            DM.TotalPrice, DM.DiscountPrice, DM.PaidPrice, DM.Outstanding, DM.PayType, DM.AllPeriods,
            DM.PayLastPeriod, DM.PayLastStatus, DM.PayPeriod, DM.EffDate, DM.ProductName, DM.ProductModel
            FROM [TSR_Application].dbo.DebtorAnalyze_Master AS DM
            LEFT JOIN [TSR_Application].dbo.DebtorAnalyze_Address AS DA ON DM.Refno = DA.Refno
            WHERE DA.TelHome = '" . $mobile . "' OR DA.TelMobile = '" . $mobile . "' OR DA.TelOffice = '" . $mobile . "'
            ORDER BY EffDate DESC";
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status" => "SUCCESS",
                "message" => "Registration",
                "data" => $stmt->result_array()
            )
            , 200
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
}
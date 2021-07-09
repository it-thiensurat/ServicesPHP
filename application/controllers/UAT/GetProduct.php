<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetProduct extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $productCode = $this->get('productcode');
        if ($productCode == '') {
            $sql = "SELECT PCM.Product_Code, PCM.Product_Name, PCM.Price, PI.Url
                FROM [TSR_ONLINE_MARKETING].dbo.V_ProductCatalog_Master AS PCM
                LEFT JOIN [TSR_ONLINE_MARKETING].dbo.ProductImage AS PI 
                ON PCM.Product_Code = PI.Product_Code";
        } else {
            $sql = "SELECT PCM.Product_Code, PCM.Product_Name, PCM.Product_Detail1, PCM.Price, PCM.Discount_Bath, 
                PCM.Discount_Percent, PCM.PromotionCode, PCM.PromotionName, PCM.PromotionImage, PCM.SellPrice, 
                PCM.PromoDiscount_Bath, PCM.PromoDiscount_Percent, PCM.StartDate, PCM.EndDate, PCM.Prosubtype_Code, 
                PCM.Prosubtype_Name_TH, PI.Url
                FROM [TSR_ONLINE_MARKETING].dbo.V_ProductCatalog_Master AS PCM
                LEFT JOIN [TSR_ONLINE_MARKETING].dbo.ProductImage AS PI 
                ON PCM.Product_Code = PI.Product_Code WHERE PCM.Product_Code = '" . $productCode . "' ";
        }
        
        $stmt = $this->db->query($sql);
        $this->response(
            array(
                "status" => "SUCCESS",
                "message" => "Product",
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
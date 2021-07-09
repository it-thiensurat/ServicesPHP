<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class OnlinePayment extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
        $this->db = $this->load->database('uat', TRUE);
        $this->sandbox_url = "https://demo2.2c2p.com/2C2PFrontEnd/RedirectV3/payment";
        // $this->sandbox_url = "t.2c2p.com/RedirectV3/payment";
    }
    
    public function index_get() {
        $pay_refno      = $this->input->get('refno');
        $pay_customerid = $this->input->get('customerid');
        $pay_order      = $this->input->get('orderid');
        $pay_desc       = $this->input->get('desc');
        $pay_amount     = $this->input->get('amount');
        $pay_period     = $this->input->get('period');

        $order_id       = $pay_order . date('YmdHi');
        $contract_no    = $pay_order;
        $amount         = $pay_amount;
        $pay_version    = "8.5";
        $merchant_id    = "JT04";
        $pay_currency   = "764";
        $secret_key     = "QnmrnH6QE23N";
        $result_url_1   = "http://toss.thiensurat.co.th/ServicesPHP/UAT/SmartcareV2/ResultPayment";

        $pay_amount     = str_replace(",","",str_replace(".", "", $pay_amount));
        $pay_amount     = str_pad($pay_amount, 12, "0", STR_PAD_LEFT);

        $params         = $pay_version.$merchant_id.$pay_desc.$order_id.$pay_currency.$pay_amount.$result_url_1;
        $hash_value     = hash_hmac('sha256',$params, $secret_key,false);
        if (!$this->createPayment($order_id, $pay_customerid, $pay_refno, $contract_no, $amount, $pay_period)) {
            ?>
            <html> 
                <body>
                    <h1 style=" margin: auto;
                    width: 50%;
                    padding: 10px;">
                    พบข้อผิดพลาดในการสร้างข้อมูลสำหรับชำระเงิน
                    </h1>
                </body>
            </html>	
            <?php
            exit();
        }
        ?>
        <html> 
            <body>
            <h2 style=" margin: auto;
            width: 50%;
            padding: 10px;">
            Loading...
            </h2>
            <form style="display:none;"  method="post" enctype="application/x-www-form-urlencoded" 
            action="<?php echo $this->sandbox_url;?>" >
                <input type="hidden" name="version" value="<?php echo $pay_version;?>"/>
                <input type="hidden" name="merchant_id" value="<?php echo $merchant_id;?>"/>
                <input type="hidden" name="currency" value="<?php echo $pay_currency;?>"/>
                <input type="hidden" name="result_url_1" value="<?php echo $result_url_1;?>"/>
                <input type="hidden" name="hash_value" value="<?php echo $hash_value;?>"/>
                PRODUCT INFO : <input type="text" name="payment_description" value="<?php echo $pay_desc;?>"  readonly/><br/>
                ORDER NO : <input type="text" name="order_id" value="<?php echo $order_id;?>"  readonly/><br/>
                AMOUNT: <input type="text" name="amount" value="<?php echo $pay_amount;?>" readonly/><br/>
            </form>  
            <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.js"></script>
            <script type="text/javascript">
                $(document).ready(function() {
                    $('form').submit();
                });
            </script>
            </body>
        </html>	
        <?php
    }

    public function createPayment($order_id, $customer_id, $refno, $contract_no, $amount, $pay_period) {
        try {
            $sql    = "INSERT INTO TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] (PAYMENT_ID, PAYMENT_CUSTOMER_ID, PAYMENT_REFNO, PAYMENT_CONTRACT_NO, PAYMENT_AMOUNT, PAYMENT_PERIOD, PAYMENT_STATUS, PAYMENT_CREATE_DATE, PAYMENT_CREATE_BY)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt   = $this->db->query($sql, array($order_id, $customer_id, $refno, $contract_no, $amount, $pay_period, 0, date('Y-m-d H:i:s'), $customer_id));
            if ($stmt) {
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

}
<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class ResultPayment extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
        $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        // $response = file_get_contents('php://input');
        // $sql    = "INSERT INTO SQLUAT.TSR_DB1.dbo.2C2P_SANDBOX_RESULT (result) VALUES (?)";
        // $stmt   = $this->db->query($sql, array($response));
        // print_r($stmt);
        $num = 148000;
        // $numformat = number_format($num, 2, '.', '');
        print_r(intval($num / 100));
    }

    public function index_post() {
        ?>
        <html> 
            <body>
            <h2 style=" margin: auto;
            width: 50%;
            padding: 10px;">
            Loading...
            </h2>
            
            </body>
        </html>	
        <?php
        $response = file_get_contents('php://input');

        $version                = $_REQUEST["version"];
        $request_timestamp      = $_REQUEST["request_timestamp"];
        $merchant_id            = $_REQUEST["merchant_id"];
        $currency               = $_REQUEST["currency"];
        $order_id               = $_REQUEST["order_id"];
        $amount                 = (int)$_REQUEST["amount"];
        $invoice_no             = $_REQUEST["invoice_no"];
        $transaction_ref        = $_REQUEST["transaction_ref"];
        $approval_code          = $_REQUEST["approval_code"];
        $eci                    = $_REQUEST["eci"];
        $transaction_datetime   = $_REQUEST["transaction_datetime"];
        $payment_channel        = $_REQUEST["payment_channel"];
        $payment_status         = $_REQUEST["payment_status"];
        $channel_response_code  = $_REQUEST["channel_response_code"];
        $channel_response_desc  = $_REQUEST["channel_response_desc"];
        $masked_pan             = $_REQUEST["masked_pan"];
        $stored_card_unique_id  = $_REQUEST["stored_card_unique_id"];
        $backend_invoice        = $_REQUEST["backend_invoice"];
        $paid_channel           = $_REQUEST["paid_channel"];
        $recurring_unique_id    = $_REQUEST["recurring_unique_id"];
        $paid_agent             = $_REQUEST["paid_agent"];
        $payment_scheme         = $_REQUEST["payment_scheme"];
        $user_defined_1         = $_REQUEST["user_defined_1"];
        $user_defined_2         = $_REQUEST["user_defined_2"];
        $user_defined_3         = $_REQUEST["user_defined_3"];
        $user_defined_4         = $_REQUEST["user_defined_4"];
        $user_defined_5         = $_REQUEST["user_defined_5"];
        $browser_info           = $_REQUEST["browser_info"];
        $ippPeriod              = $_REQUEST["ippPeriod"];
        $ippInterestType        = $_REQUEST["ippInterestType"];
        $ippInterestRate        = $_REQUEST["ippInterestRate"];
        $ippMerchantAbsorbRate  = $_REQUEST["ippMerchantAbsorbRate"];
        $payment_scheme         = $_REQUEST["payment_scheme"];
        $process_by             = $_REQUEST["process_by"];
        $sub_merchant_list      = $_REQUEST["sub_merchant_list"];
        $hash_value             = $_REQUEST["hash_value"];   
        $issuer_country         = "";   
        $issuer_bank            = ""; 
        $card_type              = $_REQUEST["card_type"]; 

        $update_date = date("Y-m-d h:i:s");

        $data = array(
            "version"               => $version,
            "request_timestamp"     => $request_timestamp,
            "merchant_id"           => $merchant_id,
            "currency"              => $currency,
            "order_id"              => $order_id,
            "amount"                => floatval(($amount / 100)),
            "invoice_no"            => $invoice_no,
            "transaction_ref"       => $transaction_ref,
            "approval_code"         => $approval_code,
            "eci"                   => $eci,
            "transaction_datetime"  => $transaction_datetime,
            "payment_channel"       => $payment_channel,
            "payment_status"        => $payment_status,
            "channel_response_code" => $channel_response_code,
            "channel_response_desc" => $channel_response_desc,
            "masked_pan"            => $masked_pan,
            "stored_card_unique_id" => $stored_card_unique_id,
            "backend_invoice"       => $backend_invoice,
            "paid_channel"          => $paid_channel,
            "paid_agent"            => $paid_agent,
            "recurring_unique_id"   => $recurring_unique_id,
            "ippPeriod"             => $ippPeriod,
            "ippInterestType"       => $ippInterestType,
            "ippInterestRate"       => $ippInterestRate,
            "ippMerchantAbsorbRate" => $ippMerchantAbsorbRate,
            "payment_scheme"        => $payment_scheme,
            "process_by"            => $process_by,
            "sub_merchant_list"     => $sub_merchant_list,
            "issuer_country"        => $issuer_country,
            "issuer_bank"           => $issuer_bank,
            "card_type"             => $card_type,
            "user_defined_1"        => $user_defined_1,
            "user_defined_2"        => $user_defined_2,
            "user_defined_3"        => $user_defined_3,
            "user_defined_4"        => $user_defined_4,
            "user_defined_5"        => $user_defined_5,
            "browser_info"          => $browser_info,
            "hash_value"            => $hash_value, 
            'json'                  => json_encode($this->input->post()),
            'date_create'           => date("Y-m-d h:i:sa"),
            'date_modify'           => date("Y-m-d h:i:sa"),
        );

        $this->db->insert('TSR_DB1.dbo.2C2P_SANDBOX_RESPONSE', $data);


        $checkHashStr = $version . $request_timestamp . $merchant_id . $order_id . 
        $invoice_no . $currency . $amount . $transaction_ref . $approval_code . 
        $eci . $transaction_datetime . $payment_channel . $payment_status . 
        $channel_response_code . $channel_response_desc . $masked_pan . 
        $stored_card_unique_id . $backend_invoice . $paid_channel . $paid_agent . 
        $recurring_unique_id . $user_defined_1 . $user_defined_2 . $user_defined_3 . 
        $user_defined_4 . $user_defined_5 . $browser_info . $ippPeriod . 
        $ippInterestType . $ippInterestRate . $ippMerchantAbsorbRate . $payment_scheme .
        $process_by . $sub_merchant_list;
            
        $SECRETKEY = "QnmrnH6QE23N";
        $checkHash = hash_hmac('sha256', $checkHashStr, $SECRETKEY, false); 

        if ($payment_status == 000) {
            $status = 1;
        } else if ($payment_status == 002) {
            $status = 2;
        } else if ($payment_status == 003) {
            $status = 3;
        } else if ($payment_status == 999) {
            $status = 4;
        }

        if(strcmp(strtolower($hash_value), strtolower($checkHash)) == 0) {
            $sql    = "UPDATE TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] SET PAYMENT_STATUS = ?, PAYMENT_UPDATE_DATE = ?, PAYMENT_UPDATE_BY = ? WHERE PAYMENT_ID = ?";
            $stmt   = $this->db->query($sql, array($status, $update_date, 999, $order_id));
            if ($stmt) {
                $sql = "SELECT * FROM TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] WHERE PAYMENT_ID = ?";
                $stmt = $this->db->query($sql, array($order_id));
                // $payTran = number_format($stmt->row()->PAYMENT_AMOUNT, 2, '.', '');
                $this->updatePayment($stmt->row()->PAYMENT_CONTRACT_NO, $stmt->row()->PAYMENT_REFNO, intval($amount / 100), $transaction_datetime, $transaction_datetime);
                $this->sendNotification($order_id, $status);
            }
        } else {
            $sql    = "UPDATE TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] SET PAYMENT_STATUS = ?, PAYMENT_UPDATE_DATE = ?, PAYMENT_UPDATE_BY = ? WHERE PAYMENT_ID = ?";
            $stmt   = $this->db->query($sql, array($status, $update_date, 999, $order_id));
            if ($stmt) {
                $this->sendNotification($order_id, $status);
            }
        }
    }

    public function sendNotification($order_id, $status) {
        $sql    = "SELECT ci.FirebaseToken FROM TSR_DB1.dbo.[2C2P_CREATE_PAYMENT] AS scp
                    LEFT JOIN TSR_DB1.dbo.CUSTOMER_INFO AS ci ON scp.PAYMENT_CUSTOMER_ID = ci.CustomerId
                    WHERE scp.PAYMENT_ID = ?";
        $stmt   = $this->db->query($sql, array($order_id));

        if ($stmt) {
            $ntf=array(
                'title'         => 'แจ้งเตือนการชำระเงิน',
                'body'          => 'ชำระเงินค่างวดสำเร็จ',
                'sound'         => 'default',
                'image'         => '',
                'click_action'  => 'slip',
                'badge'         => 1,
            );

            if ($status == 1) {
                $payload_data = array(
                    "title"     => "แจ้งเตือนการชำระเงิน",
                    "message"   => "ชำระเงินค่างวดสำเร็จ",
                    "orderid"   => $order_id
                );
            } else if ($status == 2) {
                $payload_data = array(
                    "title"     => "แจ้งเตือนการชำระเงิน",
                    "message"   => "การชำระเงินถูกปฎิเสธ",
                    "orderid"   => '2'
                );
            } else if ($status == 3) {
                $payload_data = array(
                    "title"     => "แจ้งเตือนการชำระเงิน",
                    "message"   => "ยกเลิกการชำระเงิน",
                    "orderid"   => '3'
                );
            } else if ($status == 4) {
                $payload_data = array(
                    "title"     => "แจ้งเตือนการชำระเงิน",
                    "message"   => "ล้มเหลว",
                    "orderid"   => '4'
                );
            }

            $SERVER_KEY     = 'AAAA3d0-4hE:APA91bF8Ctt131XNkOsepPfvOhmTDyFLUXf3WHxRajpo-ZuhxPErF9ayauXkv6UrZ49K6QVzjXmYT0of7hIr-m-IoeAE7fTcO_cg2Alp4gNeC6WB3FarOz4bl9JRGSSHwNQfsbH_2gOK';
            $SEND_URL       = 'https://fcm.googleapis.com/fcm/send';
            $SENDER_ID      = '952899658257';
            $token          = array($stmt->row()->FirebaseToken);
            
            $fields = array(
                'registration_ids'  => $token,
                'priority'          => 'high',
                'notification'      => $ntf,
                "collapse_key"      => "type_a",
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

    public function updatePayment($contno, $refno, $amount, $datetransfer, $paydate) {
        $sql = "exec TSRData_Source.[dbo].SP_TSSM_CreateReceiptTransfer 
                    @Empid = 'X00033',
                    @Contno = ?,
                    @ContractReferenceNo = ?,
                    @PayTran = ?,
                    @Ways = ?,
                    @DateTransfer = ?";
        // $sql = "exec TSRData_Source.[dbo].SP_TSSM_CreateReceiptTransfer 
        //             @Empid = 'X00033',
        //             @Contno = '80003532',
        //             @ContractReferenceNo = '630050254',
        //             @PayTran = 1480,
        //             @Ways = 'TEST',
        //             @DateTransfer = GETDATE()";
                    // @PayDate = ?";
        $this->db->query($sql, array($contno, $refno, $amount, $datetransfer));
        // print_r($this->db->query($sql));
    }
}
<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class PaymentTransaction extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        $this->dayTH = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
        $this->monthTH = [null,'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        $this->monthTH_brev = [null,'ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    }
    
    public function index_get() {
        
    }

    public function index_post() {
        $refno = $this->input->post('refno');
        $sql = "SELECT * FROM [TSR_DB1].dbo.CONTRACT_PERIOD
                WHERE Refno = ? ORDER BY PeriodNo ASC";
        $stmt = $this->db->query($sql, array($refno));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $arr = [];
            foreach($result as $k => $v) {
                $r = array(
                    'time'          => $this->thai_date_short_number(strtotime($v['AccDueDate'])),//date("d-m-y", strtotime($v['AccDueDate'])),
                    'title'         => 'งวดที่ ' . $v['PeriodNo'],
                    'description'   => 'จำนวนเงินที่ชำระ ' . number_format($v['Amount'], 2) . ' บาท',
                );

                array_push($arr, $r);
            }

            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Payment transaction",
                    "data"      => $arr
                )
                , 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Payment transaction",
                    "data"      => ''
                )
                , 200
            );
        }
    }
 
    public function index_put()
    {
    }
 
    public function index_delete()
    {
    }

    function thai_date_short_number($time){   // 19-12-56
        // global $dayTH,$monthTH;   
        $thai_date_return = date("d",$time);   
        $thai_date_return.="-".date("m",$time);   
        $thai_date_return.= "-".substr((date("Y",$time)+543),-2);   
        return $thai_date_return;   
    } 
}
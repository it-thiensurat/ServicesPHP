<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class PaymentSubmit extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->img_url = 'http://thiensurat.com/fileshare01';
    }

    public function index_get() {
        echo $this->config->config['base_url'];
    }

    public function index_post() {
        $date           = date('Y-m-d H:i:s');
        $empId          = $this->input->post('empId');
        $empName        = $this->input->post('empName');
        $citizen        = $this->input->post('citizen');
        $detailId       = $this->input->post('DetailID');
        $saleCode       = $this->input->post('saleCode');
        $lat            = $this->input->post('lat');
        $lon            = $this->input->post('lon');
        $paymentAmount  = $this->input->post('PaymentAmount');
        $signimg        = $this->input->post('signimg');
        $createby       = $this->input->post('createby');
        $paymentType    = $this->input->post('paymenttype');
        $paymentUrl     = "";
        $signatureUrl   = "";

        if (isset($_FILES['paymentimg']['tmp_name'])) {
            $ftp = ftp_connect('ftp.thiensurat.com');
            if ($ftp) {
                $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                if ($f) {
                    ftp_pasv($ftp, true);
                    $num_files = count($_FILES['paymentimg']['tmp_name']);
                    for($i = 0; $i < $num_files; $i++) {
                        $image_name     = $detailId . "_" . date('YmdHis');
                        $image_dest     = "/HR/SaleCheckIn/payment/pay/" . $image_name . ".jpg";
                        $image          = $_FILES['paymentimg']['tmp_name'][$i];
                        $fupload        = ftp_put($ftp, $image_dest, $image, FTP_BINARY);
                        $paymentUrl     = $this->img_url . $image_dest;
                    }
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถเชื่อมต่อ FTP ได้",
                            "data"      => $this->input->post()
                        ), 200
                    );
                    exit();
                }
            }
        }

        if ($signimg != "") {
            $ftp = ftp_connect('ftp.thiensurat.com');
            if ($ftp) {
                $f = ftp_login($ftp, 'fileshare01@thiensurat.com', 'CX8Q2Z7wO');
                if ($f) {
                    ftp_pasv($ftp, true);
                    $image_name     = $detailId . "_" . date('YmdHis');
                    $image_dest     = "/HR/SaleCheckIn/payment/sign/" . $image_name . ".jpg";
                    $image          = base64_decode($signimg);

                    $file            = "uploads/signature" . $detailId . ".jpg";
                    file_put_contents($file, $image);

                    $fupload        = ftp_put($ftp, $image_dest, $file, FTP_BINARY);
                    $signatureUrl   = $this->img_url . $image_dest;
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถเชื่อมต่อ FTP ได้",
                            "data"      => $this->input->post()
                        ), 200
                    );
                    exit();
                }
            }
        }

        $data = array(
            'DetailID'          => $detailId,
            'EmpID'             => $empId,
            'EmpName'           => $empName,
            'SaleCode'          => $saleCode,
            'CitizenID'         => $citizen,
            'Latitude'          => $lat,
            'Longitude'         => $lon,
            'PaymentTime'       => $date,
            'PaymentAmount'     => $paymentAmount,
            'PaymentImage'      => $paymentUrl,
            'PaymentSignature'  => $signatureUrl,
            'CreateDate'        => $date,
            'CreateBy'          => $createby,
        );

        if ($paymentType == "100") {
            $stmt = $this->db->insert('SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Payment', $data);
            if ($stmt) {
                if ($this->updatePayment100Detail($detailId, $empId, $paymentAmount)) {
                    $this->response(
                        array(
                            "status"    => "SUCCESS",
                            "message"   => "บันทึกการชำระเงินแล้ว",
                            "data"      => ""
                        ), 200
                    );
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อ IT",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อ IT",
                        "data"      => ""
                    ), 200
                );
            }
        } else {
            $stmt = $this->db->insert('SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Payment', $data);
            if ($stmt) {
                if ($this->updatePaymentDetail($detailId, $empId, $paymentAmount)) {
                    $this->response(
                        array(
                            "status"    => "SUCCESS",
                            "message"   => "บันทึกการจ่ายเงินแล้ว",
                            "data"      => ""
                        ), 200
                    );
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อ IT",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถบันทึกข้อมูลได้ กรุณาติดต่อ IT",
                        "data"      => ""
                    ), 200
                );
            }
        }
    }

    public function updatePaymentDetail($detailId, $empId, $paymentAmount) {
        $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work_Detail SET PaymentBalance = ?, PaymentStatus = 1 WHERE DetailID = ?";
        $stmt = $this->db->query($sql, array($paymentAmount, $detailId));
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePayment100Detail($detailId, $empId, $amount) {
        $sql = "UPDATE SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Detail SET 
                PaymentAmount = (
                    SELECT (PaymentAmount - $amount) FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Payment WHERE DetailID = ? AND EmpID = ?
                ), 
                PaymentBalance = (
                    SELECT SUM(PaymentAmount) FROM SQLUAT.TSR_DB1.dbo.SaleTeam_Work100_Payment WHERE DetailID = ? AND EmpID = ?
                ), PaymentStatus = 1 WHERE DetailID = ?";
        $stmt = $this->db->query($sql, array($detailId, $empId, $detailId, $empId, $detailId));
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    }
}
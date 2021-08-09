<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetQuotationWaitForApprove extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
        $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        $empId  = $this->input->get('empId');
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION WHERE APQ_EMP_ID = ? AND APQ_STATUS IN (0, 3, 4) ORDER BY APQ_ID DESC";
        $stmt   = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            $data = [];
            $result = $stmt->result_array();
            foreach($result as $k => $v) {
                $quotationStatus = "";
                if ($v['APQ_STATUS'] == 0) {
                    $quotationStatus = "รอการอนุมัติ";
                } else if ($v['APQ_STATUS'] == 3) {
                    $quotationStatus = "แก้ไขใบเสนอราคา";
                } else if ($v['APQ_STATUS'] == 4) {
                    $quotationStatus = "ไม่อนุมัติ";
                }

                $res = array(
                    'APQ_ID'            => $v['APQ_ID'],
                    'APCUS_ID'          => $v['APCUS_ID'],
                    'APQ_DATE'          => $v['APQ_DATE'],
                    'APQ_DISCOUNT'      => $v['APQ_DISCOUNT'],
                    'APQ_EXPIRE_DATE'   => $v['APQ_EXPIRE_DATE'],
                    'APQ_PROJECTNAME'   => $v['APQ_PROJECTNAME'] . "",
                    'APQ_STATUS'        => $v['APQ_STATUS'],
                    'APQ_COMMENT'       => $v['APQ_COMMENT'] . "",
                    'APQ_STATUS_TEXT'   => $quotationStatus,
                    'CUSTOMER_DETAIL'   => $this->getCustomerDetail($v['APCUS_ID']),
                    'PRODUCT_DETAIL'    => $this->getProductDetail($v['APQ_ID'])
                );

                array_push($data, $res);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการใบเสนอราคา",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลใบเสนอราคา",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function index_post() {
        $empId  = $this->input->post('empId');
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION WHERE APQ_EMP_ID = ? AND APQ_STATUS IN (0, 3, 4) ORDER BY APQ_ID DESC";
        $stmt   = $this->db->query($sql, array($empId));
        if ($stmt->num_rows() > 0) {
            $data = [];
            $result = $stmt->result_array();
            foreach($result as $k => $v) {
                $quotationStatus = "";
                if ($v['APQ_STATUS'] == 0) {
                    $quotationStatus = "รอการอนุมัติ";
                } else if ($v['APQ_STATUS'] == 3) {
                    $quotationStatus = "แก้ไขใบเสนอราคา";
                } else if ($v['APQ_STATUS'] == 4) {
                    $quotationStatus = "ไม่อนุมัติ";
                }

                $res = array(
                    'APQ_ID'            => $v['APQ_ID'],
                    'APCUS_ID'          => $v['APCUS_ID'],
                    'APQ_DATE'          => $v['APQ_DATE'],
                    'APQ_DISCOUNT'      => $v['APQ_DISCOUNT'],
                    'APQ_EXPIRE_DATE'   => $v['APQ_EXPIRE_DATE'],
                    'APQ_PROJECTNAME'   => $v['APQ_PROJECTNAME'] . "",
                    'APQ_STATUS'        => $v['APQ_STATUS'],
                    'APQ_COMMENT'       => $v['APQ_COMMENT'] . "",
                    'APQ_STATUS_TEXT'   => $quotationStatus,
                    'CUSTOMER_DETAIL'   => $this->getCustomerDetail($v['APCUS_ID']),
                    'PRODUCT_DETAIL'    => $this->getProductDetail($v['APQ_ID'])
                );

                array_push($data, $res);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการใบเสนอราคา",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลใบเสนอราคา",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getCustomerDetail($customer_id) {
        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER AS ac
                LEFT JOIN TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS AS ad ON ac.APCUS_ID = ad.APCUS_ID AND ad.APADDR_TYPE = 'AddressIDCard'
                WHERE ac.APCUS_ID = ?";
        $stmt = $this->db->query($sql, array($customer_id));
        if ($stmt->num_rows() > 0) {
            // return $this->getCustomerAddress($customer_id, $stmt->result_array());
            $data   = [];
            foreach($stmt->result_array() as $k => $v) {
                $res = array(
                    'APCUS_ID'              => $customer_id,
                    'APCUS_NAME'            => $v['APCUS_NAME'],
                    'APCUS_BRANCH'          => $v['APCUS_BRANCH'],
                    'APCUS_LASTNAME'        => $v['APCUS_LASTNAME'],
                    'APCUS_IDCARD'          => $v['APCUS_IDCARD'],
                    'APCUS_TYPE'            => $v['APCUS_TYPE'],
                    'APCUS_ADDR'            => $v['APADDR_ADDR'],
                    'APCUS_MOO'             => $v['APADDR_MOO'],
                    'APCUS_SOI'             => $v['APADDR_SOI'],
                    'APCUS_ROAD'            => $v['APADDR_ROAD'],
                    'APCUS_PROVINCE_ID'     => $v['APADDR_PROVINCE_ID'],
                    'APCUS_DISTRICT_ID'     => $v['APADDR_DISTRICT_ID'],
                    'APCUS_SUBDISTRICT_ID'  => $v['APADDR_SUBDISTRICT_ID'],
                    'APCUS_ZIPCODE'         => $v['APADDR_ZIPCODE'],
                    'APCUS_PHONE'           => $v['APCUS_PHONE'],
                    'APCUS_EMAIL'           => $v['APCUS_EMAIL'],
                    'APCUS_CONTACT_NAME'    => $v['APCUS_CONTACT_NAME'],
                    'APCUS_CONTACT_PHONE'   => $v['APCUS_CONTACT_PHONE'],
                    'APCUS_CONTACT_EMAIL'   => $v['APCUS_CONTACT_EMAIL'],
                    'APCUS_CHANNEL'         => intval($v['APCUS_CHANNEL']),
                    'ADDR_INSTALL'          => $this->getCustomerAddressInstall($v['APCUS_ID'])
                );

                array_push($data, $res);
            }

            return $data;
        } else {
            return [];
        }
    }

    public function getProductDetail($quotationId) {
        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION_DETAILS WHERE APQ_ID = ? AND APQD_STATUS = ?";
        $stmt = $this->db->query($sql, array($quotationId, 1));
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return [];
        }
    }

    public function getCustomerAddress($customer_id, $customer_data) {
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS WHERE APCUS_ID = ? AND APADDR_TYPE = ?";
        $stmt   = $this->db->query($sql, array($customer_id, "AddressIDCard"));

        // $customer_data['APCUS_ADDR']            = $stmt->row()->APADDR_ADDR;
        // $customer_data['APCUS_MOO']             = $stmt->row()->APADDR_MOO;
        // $customer_data['APCUS_SOI']             = $stmt->row()->APADDR_SOI;
        // $customer_data['APCUS_ROAD']            = $stmt->row()->APADDR_ROAD;
        // $customer_data['APCUS_PROVINCE_ID']     = $stmt->row()->APADDR_PROVINCE_ID;
        // $customer_data['APCUS_DISTRICT_ID']     = $stmt->row()->APADDR_DISTRICT_ID;
        // $customer_data['APCUS_SUBDISTRICT_ID']  = $stmt->row()->APADDR_SUBDISTRICT_ID;
        // $customer_data['APCUS_ZIPCODE']         = $stmt->row()->APADDR_ZIPCODE;
        // $customer_data['ADDR_INSTALL']          = $this->getCustomerAddressInstall($customer_id);
        $data   = [];
        foreach($customer_data as $k => $v) {
            $res = array(
                'APCUS_ID'              => $customer_id,
                'APCUS_NAME'            => $v['APCUS_NAME'],
                'APCUS_BRANCH'          => $v['APCUS_BRANCH'],
                'APCUS_LASTNAME'        => $v['APCUS_LASTNAME'],
                'APCUS_IDCARD'          => $v['APCUS_IDCARD'],
                'APCUS_TYPE'            => $v['APCUS_TYPE'],
                'APCUS_ADDR'            => $stmt->row()->APADDR_ADDR,
                'APCUS_MOO'             => $stmt->row()->APADDR_MOO,
                'APCUS_SOI'             => $stmt->row()->APADDR_SOI,
                'APCUS_ROAD'            => $stmt->row()->APADDR_ROAD,
                'APCUS_PROVINCE_ID'     => $stmt->row()->APADDR_PROVINCE_ID,
                'APCUS_DISTRICT_ID'     => $stmt->row()->APADDR_DISTRICT_ID,
                'APCUS_SUBDISTRICT_ID'  => $stmt->row()->APADDR_SUBDISTRICT_ID,
                'APCUS_ZIPCODE'         => $stmt->row()->APADDR_ZIPCODE,
                'APCUS_PHONE'           => $v['APCUS_PHONE'],
                'APCUS_EMAIL'           => $v['APCUS_EMAIL'],
                'APCUS_CONTACT_NAME'    => $v['APCUS_CONTACT_NAME'],
                'APCUS_CONTACT_PHONE'   => $v['APCUS_CONTACT_PHONE'],
                'APCUS_CONTACT_EMAIL'   => $v['APCUS_CONTACT_EMAIL'],
                'APCUS_CHANNEL'         => intval($v['APCUS_CHANNEL']),
                'ADDR_INSTALL'          => $this->getCustomerAddressInstall($customer_id)
            );

            array_push($data, $res);
        }

        return $data;
    }

    public function getCustomerAddressInstall($customer_id) {
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS WHERE APCUS_ID = ? AND APADDR_TYPE = ?";
        $stmt   = $this->db->query($sql, array($customer_id, "AddressInstall"));
        return $stmt->result_array();
    }
}
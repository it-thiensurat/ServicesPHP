<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class GetCustomerList extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
        $empId  = $this->input->get('empId');
        if (!isset($empId)) {
            $sql    = "SELECT *, (SELECT TOP 1 aq.APQ_ID FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac 
                    LEFT JOIN TSR_DB1.dbo.ALPINE_QUOTATION aq ON ac.APCUS_ID = aq.APCUS_ID 
                    WHERE ac.APCUS_ID = ac1.APCUS_ID ORDER BY aq.APQ_ID DESC) AS APQ_ID 
                    FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac1
                    LEFT JOIN TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS AS ad ON ac1.APCUS_ID = ad.APCUS_ID AND ad.APADDR_TYPE = 'AddressIDCard'";
            $stmt   = $this->db->query($sql);
        } else {
            $sql    = "SELECT *, ('') AS APQ_ID FROM TSR_DB1.dbo.ALPINE_CUSTOMER AS ac
                        LEFT JOIN TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS AS ad ON ac.APCUS_ID = ad.APCUS_ID AND ad.APADDR_TYPE = 'AddressIDCard'
                        WHERE ac.APCUS_CREATE_BY = ? AND ac.APCUS_STATUS = ?";
            
            $stmt   = $this->db->query($sql, array($empId, 0));
        }
        
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $res = array(
                    'APQ_ID'                => $v['APQ_ID'],
                    'APCUS_ID'              => $v['APCUS_ID'],
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
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการลูกค้า",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลลูกค้า",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function index_post() {
        $empId  = $this->input->post('empId');
        if ($empId == "") {
            // $sql    = "SELECT *, 
            //             (SELECT TOP 1 aq.APQ_ID FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac 
            //                 LEFT JOIN TSR_DB1.dbo.ALPINE_QUOTATION aq ON ac.APCUS_ID = aq.APCUS_ID 
            //                 WHERE ac.APCUS_ID = ac1.APCUS_ID ORDER BY aq.APQ_ID DESC
            //             ) AS APQ_ID 
            //             FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac1";
            $sql    = "SELECT *, (SELECT TOP 1 aq.APQ_ID FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac 
                    LEFT JOIN TSR_DB1.dbo.ALPINE_QUOTATION aq ON ac.APCUS_ID = aq.APCUS_ID 
                    WHERE ac.APCUS_ID = ac1.APCUS_ID ORDER BY aq.APQ_ID DESC) AS APQ_ID 
                    FROM TSR_DB1.dbo.ALPINE_CUSTOMER ac1
                    LEFT JOIN TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS AS ad ON ac1.APCUS_ID = ad.APCUS_ID AND ad.APADDR_TYPE = 'AddressIDCard'";
            $stmt   = $this->db->query($sql);
        } else {
            // $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_CREATE_BY = ? AND APCUS_STATUS = ?";
            $sql    = "SELECT *, ('') AS APQ_ID FROM TSR_DB1.dbo.ALPINE_CUSTOMER AS ac
                        LEFT JOIN TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS AS ad ON ac.APCUS_ID = ad.APCUS_ID AND ad.APADDR_TYPE = 'AddressIDCard'
                        WHERE ac.APCUS_CREATE_BY = ? AND ac.APCUS_STATUS = ?";
            $stmt   = $this->db->query($sql, array($empId, 0));
        }
        
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $res = array(
                    'APQ_ID'                => $v['APQ_ID'],
                    'APCUS_ID'              => $v['APCUS_ID'],
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
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "รายการลูกค้า",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "ไม่มีข้อมูลลูกค้า",
                    "data"      => ''
                ), 200
            );
        }
    }

    public function getProduct($quotationId) {
        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_QUOTATION_DETAILS WHERE APQD_STATUS = 1";
        $stmt = $this->db->query($sql);
        if ($stmt->num_rows() > 0) {
            return $stmt->result_array();
        } else {
            return null;
        }
    }

    public function getCustomerAddress($customer_id, $customer_data) {
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS WHERE APCUS_ID = ? AND APADDR_TYPE = ?";
        $stmt   = $this->db->query($sql, array($customer_id, "AddressIDCard"));

        // $customer_data[0]['APCUS_ADDR']            = $stmt->row()->APADDR_ADDR;
        // $customer_data[0]['APCUS_MOO']             = $stmt->row()->APADDR_MOO;
        // $customer_data[0]['APCUS_SOI']             = $stmt->row()->APADDR_SOI;
        // $customer_data[0]['APCUS_ROAD']            = $stmt->row()->APADDR_ROAD;
        // $customer_data[0]['APCUS_PROVINCE_ID']     = $stmt->row()->APADDR_PROVINCE_ID;
        // $customer_data[0]['APCUS_DISTRICT_ID']     = $stmt->row()->APADDR_DISTRICT_ID;
        // $customer_data[0]['APCUS_SUBDISTRICT_ID']  = $stmt->row()->APADDR_SUBDISTRICT_ID;
        // $customer_data[0]['APCUS_ZIPCODE']         = $stmt->row()->APADDR_ZIPCODE;
        // $customer_data[0]['ADDR_INSTALL']          = $this->getCustomerAddressInstall($customer_id);

        // return $customer_data;

        $data   = [];
        foreach($customer_data as $k => $v) {
            $res = array(
                'APCUS_ID'              => $v['APCUS_ID'],
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
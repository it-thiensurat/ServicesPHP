<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AddCustomer extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
         $this->load->database();
        // $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        echo 'AddCustomer';
    }

    public function index_post() {
        $this->db->trans_start();
        $customer_id                = $this->input->post('customerId');
        $customer_type              = $this->input->post('type');
        $customer_name              = $this->input->post('name');
        $customer_lastname          = $this->input->post('lastname');
        $customer_tax               = $this->input->post('tax');
        $customer_addr              = $this->input->post('addr');
        $customer_moo               = $this->input->post('moo');
        $customer_soi               = $this->input->post('soi');
        $customer_road              = $this->input->post('road');
        $customer_provice           = $this->input->post('province');
        $customer_district          = $this->input->post('district');
        $customer_subdistrict       = $this->input->post('subdistrict');
        $customer_zipcode           = $this->input->post('zipcode');
        $customer_phone             = $this->input->post('phone');
        $customer_email             = $this->input->post('email');
        $customer_contactname       = $this->input->post('contactname');
        $customer_contactphone      = $this->input->post('contactphone');
        $customer_contactemail      = $this->input->post('contactemail');
        $emp_id                     = $this->input->post('empid');
        $actionType                 = $this->input->post('actionType');
        $dataChannel                = $this->input->post('dataChannel');
        $addressType                = $this->input->post('addressType');
        $create_date                = date('Y-m-d H:i:s');

        if ($actionType == "new") {
            if ($addressType == "AddressInstall") {
                if ($this->checkAddress($customer_id, $addressType)) {
                    $dataAddr = array(
                        'APADDR_ADDR'            => $customer_addr,
                        'APADDR_MOO'             => $customer_moo,
                        'APADDR_SOI'             => $customer_soi,
                        'APADDR_ROAD'            => $customer_road,
                        'APADDR_PROVINCE_ID'     => $customer_provice,
                        'APADDR_DISTRICT_ID'     => $customer_district,
                        'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                        'APADDR_ZIPCODE'         => $customer_zipcode,
                        'APADDR_CREATE_BY'       => $emp_id,
                    );
                    $this->db->where('APCUS_ID', $customer_id);
                    $this->db->where('APADDR_TYPE', $addressType);
                    if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) {
                        $this->db->trans_commit();
                        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                        $stmt = $this->db->query($sql, array($customer_id));
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกข้อมูลสำเร็จ",
                                "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                            ), 200
                        );
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    $dataAddr = array(
                        'APCUS_ID'               => $customer_id,
                        'APADDR_TYPE'            => $addressType,
                        'APADDR_ADDR'            => $customer_addr,
                        'APADDR_MOO'             => $customer_moo,
                        'APADDR_SOI'             => $customer_soi,
                        'APADDR_ROAD'            => $customer_road,
                        'APADDR_PROVINCE_ID'     => $customer_provice,
                        'APADDR_DISTRICT_ID'     => $customer_district,
                        'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                        'APADDR_ZIPCODE'         => $customer_zipcode,
                        'APADDR_CREATE_BY'       => $emp_id,
                    );
                    if ($this->db->insert('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) { 
                        $this->db->trans_commit();
                        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                        $stmt = $this->db->query($sql, array($customer_id));
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกข้อมูลสำเร็จ",
                                "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                            ), 200
                        );
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                }
            } else {
                if ($customer_type == "1") {
                    $data = array(
                        'APCUS_NAME'            => $customer_name,
                        'APCUS_BRANCH'          => $customer_lastname,
                        'APCUS_IDCARD'          => $customer_tax,
                        'APCUS_TYPE'            => $customer_type,
                        'APCUS_PHONE'           => $customer_phone,
                        'APCUS_EMAIL'           => $customer_email,
                        'APCUS_CONTACT_NAME'    => $customer_contactname,
                        'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                        'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                        'APCUS_STATUS'          => 0,
                        'APCUS_CREATE_BY'       => $emp_id,
                        'APCUS_CHANNEL'         => intval($dataChannel)
                    );
                } else {
                    $data = array(
                        'APCUS_NAME'            => $customer_name,
                        'APCUS_LASTNAME'        => $customer_lastname,
                        'APCUS_IDCARD'          => $customer_tax,
                        'APCUS_TYPE'            => $customer_type,
                        'APCUS_PHONE'           => $customer_phone,
                        'APCUS_EMAIL'           => $customer_email,
                        'APCUS_CONTACT_NAME'    => $customer_contactname,
                        'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                        'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                        'APCUS_STATUS'          => 0,
                        'APCUS_CREATE_BY'       => $emp_id,
                        'APCUS_CHANNEL'         => intval($dataChannel)
                    );
                }
                
                if ($customer_id != "") {
                    $this->db->where('APCUS_ID', $customer_id);
                    if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
                        $dataAddr = array(
                            'APCUS_ID'               => $customer_id,
                            'APADDR_TYPE'            => $addressType,
                            'APADDR_ADDR'            => $customer_addr,
                            'APADDR_MOO'             => $customer_moo,
                            'APADDR_SOI'             => $customer_soi,
                            'APADDR_ROAD'            => $customer_road,
                            'APADDR_PROVINCE_ID'     => $customer_provice,
                            'APADDR_DISTRICT_ID'     => $customer_district,
                            'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                            'APADDR_ZIPCODE'         => $customer_zipcode,
                            'APADDR_CREATE_BY'       => $emp_id,
                        );

                        $this->db->where('APCUS_ID', $customer_id);
                        $this->db->where('APADDR_TYPE', $addressType);
                        if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) {
                            $this->db->trans_commit();
                            $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                            $stmt = $this->db->query($sql, array($customer_id));
                            $this->response(
                                array(
                                    "status"    => "SUCCESS",
                                    "message"   => "บันทึกข้อมูลสำเร็จ",
                                    "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                                ), 200
                            );
                        } else {
                            $this->db->trans_rollback();
                            $this->response(
                                array(
                                    "status"    => "FAILED",
                                    "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                    "data"      => ""
                                ), 200
                            );
                        }
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    if ($this->db->insert('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
                        $apcus_id = $this->db->insert_id();
                        $dataAddr = array(
                            'APCUS_ID'               => $apcus_id,
                            'APADDR_TYPE'            => $addressType,
                            'APADDR_ADDR'            => $customer_addr,
                            'APADDR_MOO'             => $customer_moo,
                            'APADDR_SOI'             => $customer_soi,
                            'APADDR_ROAD'            => $customer_road,
                            'APADDR_PROVINCE_ID'     => $customer_provice,
                            'APADDR_DISTRICT_ID'     => $customer_district,
                            'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                            'APADDR_ZIPCODE'         => $customer_zipcode,
                            'APADDR_CREATE_BY'       => $emp_id,
                        );
        
                        if ($this->db->insert('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) {
                            $this->db->trans_commit();
                            $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                            $stmt = $this->db->query($sql, array($apcus_id));
                            $this->response(
                                array(
                                    "status"    => "SUCCESS",
                                    "message"   => "บันทึกข้อมูลสำเร็จ",
                                    "data"      => $this->getCustomerAddress($apcus_id, $stmt->result_array())
                                ), 200
                            );
                        } else {
                            $this->db->trans_rollback();
                            $this->response(
                                array(
                                    "status"    => "FAILED",
                                    "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                    "data"      => ""
                                ), 200
                            );
                        }
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                }
            }
        } else {
            if ($addressType == "AddressInstall") {
                if ($this->checkAddress($customer_id, $addressType)) {
                    $dataAddr = array(
                        'APADDR_ADDR'            => $customer_addr,
                        'APADDR_MOO'             => $customer_moo,
                        'APADDR_SOI'             => $customer_soi,
                        'APADDR_ROAD'            => $customer_road,
                        'APADDR_PROVINCE_ID'     => $customer_provice,
                        'APADDR_DISTRICT_ID'     => $customer_district,
                        'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                        'APADDR_ZIPCODE'         => $customer_zipcode,
                    );

                    $this->db->where('APCUS_ID', $customer_id);
                    $this->db->where('APADDR_TYPE', $addressType);
                    if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) {
                        $this->db->trans_commit();
                        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                        $stmt = $this->db->query($sql, array($customer_id));
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกข้อมูลสำเร็จ",
                                "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                            ), 200
                        );
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    $dataAddr = array(
                        'APCUS_ID'               => $customer_id,
                        'APADDR_TYPE'            => $addressType,
                        'APADDR_ADDR'            => $customer_addr,
                        'APADDR_MOO'             => $customer_moo,
                        'APADDR_SOI'             => $customer_soi,
                        'APADDR_ROAD'            => $customer_road,
                        'APADDR_PROVINCE_ID'     => $customer_provice,
                        'APADDR_DISTRICT_ID'     => $customer_district,
                        'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                        'APADDR_ZIPCODE'         => $customer_zipcode,
                        'APADDR_CREATE_BY'       => $emp_id,
                    );
                    if ($this->db->insert('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) { 
                        $this->db->trans_commit();
                        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                        $stmt = $this->db->query($sql, array($customer_id));
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "บันทึกข้อมูลสำเร็จ",
                                "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                            ), 200
                        );
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                }
            } else {
                if ($customer_type == "1") {
                    $data = array(
                        'APCUS_NAME'            => $customer_name,
                        'APCUS_BRANCH'          => $customer_lastname,
                        'APCUS_IDCARD'          => $customer_tax,
                        'APCUS_TYPE'            => $customer_type,
                        'APCUS_PHONE'           => $customer_phone,
                        'APCUS_EMAIL'           => $customer_email,
                        'APCUS_CONTACT_NAME'    => $customer_contactname,
                        'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                        'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                        'APCUS_UPDATE_DATE'     => $create_date,
                        'APCUS_UPDATE_BY'       => $emp_id,
                        'APCUS_CHANNEL'         => intval($dataChannel)
                    );
                } else {
                    $data = array(
                        'APCUS_NAME'            => $customer_name,
                        'APCUS_LASTNAME'        => $customer_lastname,
                        'APCUS_IDCARD'          => $customer_tax,
                        'APCUS_TYPE'            => $customer_type,
                        'APCUS_PHONE'           => $customer_phone,
                        'APCUS_EMAIL'           => $customer_email,
                        'APCUS_CONTACT_NAME'    => $customer_contactname,
                        'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                        'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                        'APCUS_UPDATE_DATE'     => $create_date,
                        'APCUS_UPDATE_BY'       => $emp_id,
                        'APCUS_CHANNEL'         => intval($dataChannel)
                    );
                }
    
                $this->db->where('APCUS_ID', $customer_id);
                if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
                    $dataAddr = array(
                        'APADDR_ADDR'            => $customer_addr,
                        'APADDR_MOO'             => $customer_moo,
                        'APADDR_SOI'             => $customer_soi,
                        'APADDR_ROAD'            => $customer_road,
                        'APADDR_PROVINCE_ID'     => $customer_provice,
                        'APADDR_DISTRICT_ID'     => $customer_district,
                        'APADDR_SUBDISTRICT_ID'  => $customer_subdistrict,
                        'APADDR_ZIPCODE'         => $customer_zipcode,
                    );

                    $this->db->where('APCUS_ID', $customer_id);
                    $this->db->where('APADDR_TYPE', $addressType);
                    if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS', $dataAddr)) {
                        $this->db->trans_commit();
                        // $data['APCUS_ID']   = $customer_id;
                        $sql = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER WHERE APCUS_ID = ?";
                        $stmt = $this->db->query($sql, array($customer_id));
                        $this->response(
                            array(
                                "status"    => "SUCCESS",
                                "message"   => "ปรับปรุงข้อมูลสำเร็จ",
                                "data"      => $this->getCustomerAddress($customer_id, $stmt->result_array())
                            ), 200
                        );
                    } else {
                        $this->db->trans_rollback();
                        $this->response(
                            array(
                                "status"    => "FAILED",
                                "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                                "data"      => ""
                            ), 200
                        );
                    }
                } else {
                    $this->db->trans_rollback();
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "ไม่สามารถบันทึกข้อมูลได้",
                            "data"      => ""
                        ), 200
                    );
                }
            }
        }
    }

    public function getCustomerAddress($customer_id, $customer_data) {
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS WHERE APCUS_ID = ? AND APADDR_TYPE = ?";
        $stmt   = $this->db->query($sql, array($customer_id, "AddressIDCard"));
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

    public function checkAddress($customer_id, $addressType) {
        $sql    = "SELECT * FROM TSR_DB1.dbo.ALPINE_CUSTOMER_ADDRESS WHERE APCUS_ID = ? AND APADDR_TYPE = ?";
        $stmt   = $this->db->query($sql, array($customer_id, $addressType));
        if ($stmt->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
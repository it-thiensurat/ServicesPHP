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
         //$this->load->database();
        $this->db = $this->load->database('uat', TRUE);
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
        $create_date                = date('Y-m-d H:i:s');
        
        if ($actionType == "new") {
            if ($customer_type == "1") {
                $data = array(
                    'APCUS_NAME'            => $customer_name,
                    'APCUS_BRANCH'          => $customer_lastname,
                    'APCUS_IDCARD'          => $customer_tax,
                    'APCUS_TYPE'            => $customer_type,
                    'APCUS_ADDR'            => $customer_addr,
                    'APCUS_MOO'             => $customer_moo,
                    'APCUS_SOI'             => $customer_soi,
                    'APCUS_ROAD'            => $customer_road,
                    'APCUS_PROVINCE_ID'     => $customer_provice,
                    'APCUS_DISTRICT_ID'     => $customer_district,
                    'APCUS_SUBDISTRICT_ID'  => $customer_subdistrict,
                    'APCUS_ZIPCODE'         => $customer_zipcode,
                    'APCUS_PHONE'           => $customer_phone,
                    'APCUS_EMAIL'           => $customer_email,
                    'APCUS_CONTACT_NAME'    => $customer_contactname,
                    'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                    'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                    'APCUS_STATUS'          => 0,
                    'APCUS_CREATE_BY'       => $emp_id,
                );
            } else {
                $data = array(
                    'APCUS_NAME'            => $customer_name,
                    'APCUS_LASTNAME'        => $customer_lastname,
                    'APCUS_IDCARD'          => $customer_tax,
                    'APCUS_TYPE'            => $customer_type,
                    'APCUS_ADDR'            => $customer_addr,
                    'APCUS_MOO'             => $customer_moo,
                    'APCUS_SOI'             => $customer_soi,
                    'APCUS_ROAD'            => $customer_road,
                    'APCUS_PROVINCE_ID'     => $customer_provice,
                    'APCUS_DISTRICT_ID'     => $customer_district,
                    'APCUS_SUBDISTRICT_ID'  => $customer_subdistrict,
                    'APCUS_ZIPCODE'         => $customer_zipcode,
                    'APCUS_PHONE'           => $customer_phone,
                    'APCUS_EMAIL'           => $customer_email,
                    'APCUS_CONTACT_NAME'    => $customer_contactname,
                    'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                    'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                    'APCUS_STATUS'          => 0,
                    'APCUS_CREATE_BY'       => $emp_id,
                );
            }
            
            if ($this->db->insert('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
                $this->db->trans_commit();
                $data['APCUS_ID']   = $this->db->insert_id();
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกข้อมูลสำเร็จ",
                        "data"      => $data
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
        } else if ($actionType == "edit") {
            if ($customer_type == "1") {
                $data = array(
                    'APCUS_NAME'            => $customer_name,
                    'APCUS_BRANCH'          => $customer_lastname,
                    'APCUS_IDCARD'          => $customer_tax,
                    'APCUS_TYPE'            => $customer_type,
                    'APCUS_ADDR'            => $customer_addr,
                    'APCUS_MOO'             => $customer_moo,
                    'APCUS_SOI'             => $customer_soi,
                    'APCUS_ROAD'            => $customer_road,
                    'APCUS_PROVINCE_ID'     => $customer_provice,
                    'APCUS_DISTRICT_ID'     => $customer_district,
                    'APCUS_SUBDISTRICT_ID'  => $customer_subdistrict,
                    'APCUS_ZIPCODE'         => $customer_zipcode,
                    'APCUS_PHONE'           => $customer_phone,
                    'APCUS_EMAIL'           => $customer_email,
                    'APCUS_CONTACT_NAME'    => $customer_contactname,
                    'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                    'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                    'APCUS_STATUS'          => 0,
                    'APCUS_UPDATE_DATE'     => $create_date,
                    'APCUS_UPDATE_BY'       => $emp_id,
                );
            } else {
                $data = array(
                    'APCUS_NAME'            => $customer_name,
                    'APCUS_LASTNAME'        => $customer_lastname,
                    'APCUS_IDCARD'          => $customer_tax,
                    'APCUS_TYPE'            => $customer_type,
                    'APCUS_ADDR'            => $customer_addr,
                    'APCUS_MOO'             => $customer_moo,
                    'APCUS_SOI'             => $customer_soi,
                    'APCUS_ROAD'            => $customer_road,
                    'APCUS_PROVINCE_ID'     => $customer_provice,
                    'APCUS_DISTRICT_ID'     => $customer_district,
                    'APCUS_SUBDISTRICT_ID'  => $customer_subdistrict,
                    'APCUS_ZIPCODE'         => $customer_zipcode,
                    'APCUS_PHONE'           => $customer_phone,
                    'APCUS_EMAIL'           => $customer_email,
                    'APCUS_CONTACT_NAME'    => $customer_contactname,
                    'APCUS_CONTACT_PHONE'   => $customer_contactphone,
                    'APCUS_CONTACT_EMAIL'   => $customer_contactemail,
                    'APCUS_STATUS'          => 0,
                    'APCUS_UPDATE_DATE'     => $create_date,
                    'APCUS_UPDATE_BY'       => $emp_id,
                );
            }

            $this->db->where('APCUS_ID', $customer_id);
            if ($this->db->update('TSR_DB1.dbo.ALPINE_CUSTOMER', $data)) {
                $this->db->trans_commit();
                $data['APCUS_ID']   = $customer_id;
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "ปรับปรุงข้อมูลสำเร็จ",
                        "data"      => $data
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
    }
}
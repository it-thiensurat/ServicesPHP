<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

class GetList extends REST_Controller { 

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function index_get() {
    
    }

    public function index_post() {
        $empId      = $this->input->post('empid');
        $referrence = $this->input->post('ref');
        $activity   = $this->input->post('activity');

        if ($activity == '') {
            $activity = 6;
        }

        $sql    = "EXEC TSR_Application.dbo.CRD_AssignArea_Web_Search @empid = ?, @actid = ?, @WhereValues = ?";
        $stmt   = $this->db->query($sql, array($empId, $activity, $referrence));

        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $data = [];
            foreach($result as $k => $v) {
                $res = array(
                    "AccNo"         => $v['AccNo'],
                    "RefNo"         => $v['RefNo'],
                    "CustName"      => $v['CustName'],
                    "Area"          => $v['Area'],
                    "Emp"           => $v['Emp'],
                    "AreaCode"      => $v['AreaCode'],
                    "EmpID"         => $v['EmpID'],
                    "f_id"          => $v['f_id'],
                    "telno"         => $v['telno'],
                    "telno1"        => $v['telno1'],
                    "telno2"        => $v['telno2'],
                    "TelAll"        => $v['TelAll'],
                    "serialno"      => $v['serialno'],
                    "setupdate"     => $v['setupdate'],
                    "setupdate_th"  => $v['setupdate_th'],
                    "actid"         => $v['actid'],
                    "remark"        => $v['remark'],
                    "assign_id"     => $v['assign_id'],
                    "app_date"      => $v['app_date'],
                    "map_img"       => $this->getMapImage($v['f_id'])
                );

                array_push($data, $res);
            }
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Get list successfull.",
                    "data"      => $data
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Get list failed.",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function getMapImage($fid) {
        $url = 'http://thiensurat.com/fileshare01/ccfc/';
        $sql    = "EXEC TSR_Application.dbo.CRD_Table_Files_Get @f_id = ?";
        $stmt   = $this->db->query($sql, array($fid));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $mapLink = '';
            foreach($result as $k => $v) {
                $mapLink = $url . $v['f_year'] . '/' . $v['f_month'] . '/' . $v['ftp_fname'] . $v['f_ext'];
            }
            return $mapLink;
        }
    }
}
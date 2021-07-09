<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') or exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class TrackingLocationUAT extends REST_Controller { 
    public function __construct() {
        parent::__construct();
		$this->load->database();
    }
    
    public function index_get() {
        $lat        = $this->input->get('latitude');
        $lon        = $this->input->get('longitude');
        $deviceid   = $this->input->get('deviceId');
        $empid      = $this->input->get('empId');
        $speed      = $this->input->get('speed');
        $source     = $this->input->get('source');

        $sql = "INSERT INTO [TSR_DB1].dbo.EMPLOYEE_LOCATOR_SYS (device_id, lat, long, speed, Source, emp_id, EmployeeName, SaleCode, TeamCode, SubDepartmentCode, DepartmentName)
        SELECT ?, ?, ?, ?, ?, EmpID
        ,(SELECT TOP (1) EmployeeName FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS EmployeeName
        ,(SELECT TOP (1) SaleCode FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS SaleCode
        ,(SELECT TOP (1) TeamCode FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS TeamCode
        ,(SELECT TOP (1) CASE WHEN ProcessType = 'Credit' OR ProcessType = 'Dept' THEN LEFT(teamcode, 3) ELSE LEFT(teamcode, 2) END  FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS SubDepartmentCode
        ,(SELECT TOP (1) ProcessType  FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS DepartmentName
       FROM TSS_PRD.Bighead_Mobile.dbo.Employee AS E
       WHERE EmpID = ?";

        $stmt = $this->db->query($sql, array($deviceid, $lat, $lon, $speed, $source, $empid));
        // $stmt = $this->db->query($sql, array($deviceid, $lat, $lon, $speed, $empid));

        if ($stmt) {
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Tracking",
                    "data"      => $this->get(),
                    'query'     => $sql
                ), REST_Controller::HTTP_OK
            );
        } else {
            $sql = "INSERT INTO [TSR_DB1].dbo.EMPLOYEE_LOCATOR_SYS (device_id, lat, long, speed, Source, emp_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, array($deviceid, $lat, $lon, $speed, $source, $empid));
            if ($stmt) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "Tracking",
                        "data"      => $this->get()
                    ), REST_Controller::HTTP_OK
                );
            } else {
                $this->response(
                    array(
                        'status' 	=> 'FAILED'
                        ,'message' 	=> 'ไม่สามารถระบุตำแหน่งได้'
                        ,'data' 	=> ''
                    ), REST_Controller::HTTP_ERROR
                );
            }
        }
    }

    public function index_post() {
        $json = json_decode(file_get_contents('php://input'), true);
        $lat        = '';
        $lon        = '';
        $deviceid   = '';
        $empid      = '';
        $source     = '';
        $speed      = '';
        foreach($json as $v) {
            if (isset($v["latitude"])) {
                $lat        = $v["latitude"];
                $lon        = $v["longitude"];
                $deviceid   = $v["deviceId"];
                $empid      = $v["empId"];
                $source     = $v["source"];
                $speed      = $v["speed"];
            }
        }

        $this->db->trans_begin();
        $sql = "INSERT INTO SQLUAT.TSR_DB1.dbo.EMPLOYEE_LOCATOR_SYS (device_id, lat, long, speed, Source, emp_id, EmployeeName, SaleCode, TeamCode, SubDepartmentCode, DepartmentName)
            SELECT ?, ?, ?, ?, ?, EmpID
            ,(SELECT TOP (1) EmployeeName FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS EmployeeName
            ,(SELECT TOP (1) SaleCode FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS SaleCode
            ,(SELECT TOP (1) TeamCode FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS TeamCode
            ,(SELECT TOP (1) CASE WHEN ProcessType = 'Credit' OR ProcessType = 'Dept' THEN LEFT(teamcode, 3) ELSE LEFT(teamcode, 2) END  FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS SubDepartmentCode
            ,(SELECT TOP (1) ProcessType  FROM TSS_PRD.Bighead_Mobile.dbo.EmployeeDetail AS E1 WHERE (EmployeeCode = e.EmpID) ORDER BY SaleCode DESC) AS DepartmentName
        FROM TSS_PRD.Bighead_Mobile.dbo.Employee AS E
        WHERE EmpID = ?";
        $stmt = $this->db->query($sql, array($deviceid, $lat, $lon, $speed, 'UAT', $empid));
        if ($stmt) {
            $this->db->trans_commit();
            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Tracking",
                    "data"      => $this->get()
                ), REST_Controller::HTTP_OK
            );
        } else {
            $sql = "INSERT INTO SQLUAT.TSR_DB1.dbo.EMPLOYEE_LOCATOR_SYS (device_id, lat, long, speed, Source, emp_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->query($sql, array($deviceid, $lat, $lon, $speed, $source, $empid));
            if ($stmt) {
                $this->db->trans_commit();
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "Tracking",
                        "data"      => $this->get()
                    ), REST_Controller::HTTP_OK
                );
            } else {
                $this->db->trans_rollback();
                $this->response(
                    array(
                        'status' 	=> 'FAILED'
                        ,'message' 	=> 'ไม่สามารถระบุตำแหน่งได้'
                        ,'data' 	=> ''
                    ), REST_Controller::HTTP_ERROR
                );
            }
            
        }
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }
}
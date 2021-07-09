<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/CreatorJwt.php');
require(APPPATH.'libraries/Format.php');

class Authen extends REST_Controller { 

    public function __construct() {
        parent::__construct();

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: *");

        $this->load->database();
        $this->jwt = new CreatorJwt();
        $this->apiKey = $this->config->item('key_token');
        try {
            $author = false;
            $token = $this->input->request_headers();
            $apiKey = '';
            $authorization = '';
            foreach($token as $key => $value) {
                if ($key == 'Authorization') {
                    $authorization = $value;
                }

                if ($key == 'X-Api-Key') {
                    $apiKey = $value;
                }
            }

            if ($apiKey != '') {
                if ($this->ApiModel->checkApiKey($apiKey)) {
                    if ($authorization != '') {
                        $this->authenWithToken($authorization, $apiKey);
                        return;
                    } else {
                        return;
                    }
                } else {
                    $this->response(
                        array(
                            "status"    => "FAILED",
                            "message"   => "Key สำหรับเรียกใช้ api ไม่ถูกต้อง",
                            "token"     => "",
                            "data"      => ""
                        ), 200
                    );
                }
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "กรุณาระบุ Key สำหรับเรียกใช้ Api.",
                        "token"     => "",
                        "data"      => ""
                    ), 200
                );
            }
        } catch(Exception $e) {
            $output = array("Exception" => $e->getMessage());
            $this->response($output, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
    
    public function index_get() {
        $username = $this->input->get('username');
        $password = $this->input->get('password');
        $this->authenEmpID($username, $password);
        // if ($this->checkEmpID($username)) {
        //     $this->authenEmpID($username, $password);
        // } else {
        //     $this->authenLDAP($username, $password);
        // }
    }

    public function authenWithToken($token, $key) {
        $data = $this->jwt->DecodeToken($token, $key);
        $username = $data['username'];
        $password = $data['password'];
        $this->authenEmpID($username, $password);
        // if ($this->checkEmpID($username)) {
        //     $this->authenEmpID($username, $password);
        // } else {
        //     $this->authenLDAP($username, $password);
        // }
    }

    public function index_post() {
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $this->authenEmpID($username, $password);
        // if ($this->checkEmpID($username)) {
        //     $this->authenEmpID($username, $password);
        // } else {
        //     $this->authenLDAP($username, $password);
        // }
    }

    public function authenEmpID($username, $password) {
        $empid  = '';
        $cardid = '';
        $divisionid = '';
        $firstname = '';
        $lastname = '';
        // $sql = "SELECT * FROM TSR_Application.dbo.TSR_Full_Employee_Sync_AD AS TF 
        //         LEFT JOIN TSR_Application.dbo.TSR_Full_EmployeeLogic AS TE ON TF.empid = TE.empid
        //         WHERE TF.empid = ? AND convert(varchar, TE.BirthDay, 112) = ?";

        // $sql = "SELECT EmpId, PreName, NameThai, SurName, CardID, positid, PositName, DepartId, DepartName, DivisionId, DivisionName, CompanyId, CompanyName
        //         FROM TSR_Application.app.TSR_Full_EmployeeLogic2
        //         WHERE EmpId = ? AND convert(varchar, BirthDay, 112) = ?";

        // $sql = "SELECT EmpId, PreName, NameThai, SurName, CardID, positid, PositName, DepartId, DepartName, 
        //             DivisionId, DivisionName, CompanyId, CompanyName, DistanceCheckIn, CameraCheckIn, DistanceCheckOut, CameraCheckOut, BranchName, BranchCode, Latitude, Longitude, DistanceIn, DistanceOut, StatusId, Status 
        //         FROM TSR_DB1.dbo.V_HRM_FULL_EMPLOYEELOGIC 
        //         WHERE EmpId = ? AND convert(varchar, BirthDay, 112) = ?";

        // $sql = "SELECT HRM.EmpId, HRM.PreName, HRM.NameThai, HRM.SurName, HRM.CardID, HRM.positid, HRM.PositName, HRM.DepartId, HRM.DepartName, 
        //         HRM.DivisionId, HRM.DivisionName, HRM.CompanyId, HRM.CompanyName, HRM.DistanceCheckIn, HRM.CameraCheckIn, HRM.DistanceCheckOut, 
        //         HRM.CameraCheckOut, HRM.BranchName, HRM.BranchCode, HRM.Latitude, HRM.Longitude, HRM.DistanceIn, HRM.DistanceOut, HRM.StatusId, HRM.Status,
        //         sl.SaleCode, sl.TeamNo, sl.PosID,sl.DepID,sl.FnYear,sl.FnNo, LEFT(sl.TeamNo, 4) AS TeamCode
        //         FROM TSR_DB1.dbo.V_HRM_FULL_EMPLOYEELOGIC AS HRM LEFT JOIN (
        //         SELECT S.SaleCode,ISNULL(LEFT(S.SaleCode,4), '-')+ISNULL(cast(S.TeamNo AS varchar(4)),'-') AS TeamNo
        //         ,S.PosID,S.DepID,S.FnYear,S.FnNo, S.SaleEmp
        //         FROM TSR_Application.dbo.NPT_Sale_Log AS S WITH(NOLOCK)
        //         INNER JOIN (
        //         SELECT f.Fortnight_year as FnYear,f.Fortnight_no as FnNo,DepID
        //         FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName2 as f
        //         WHERE (CONVERT(VARCHAR(10),GETDATE(),121) BETWEEN F.StartDate AND F.finishdate2) AND f.DepID in (1,37,38,40,52)
        //         ) AS F ON F.DepID = s.DepID AND S.FnYear = F.FnYear AND S.FnNo = F.FnNo
        //         WHERE S.SaleEmp = '" . $username . "' AND S.SaleStatus != 'R'
        //         ) AS sl ON sl.SaleEmp = HRM.EmpId
        //         WHERE HRM.EmpId = ? AND convert(varchar, BirthDay, 112) = ?";

        $sql = "SELECT HRM.EmpId, HRM.PreName, HRM.NameThai, HRM.SurName, HRM.CardID, HRM.positid, HRM.PositName, HRM.DepartId, HRM.DepartName, 
                HRM.DivisionId, HRM.DivisionName, HRM.CompanyId, HRM.CompanyName, HRM.DistanceCheckIn, HRM.CameraCheckIn, HRM.DistanceCheckOut, 
                HRM.CameraCheckOut, HRM.BranchName, HRM.BranchCode, HRM.Latitude, HRM.Longitude, HRM.DistanceIn, HRM.DistanceOut, HRM.StatusId, HRM.Status,
                sl.SaleCode, sl.TeamNo, sl.PosID,sl.DepID,sl.FnYear,sl.FnNo, LEFT(sl.TeamNo, 4) AS TeamCode
                FROM TSR_DB1.dbo.V_HRM_FULL_EMPLOYEELOGIC AS HRM LEFT JOIN (
                SELECT S.SaleCode,MAX(ISNULL(LEFT(S.SaleCode,4), '-')+ISNULL(cast(S.TeamNo AS varchar(4)),'-')) AS TeamNo
                ,(CASE WHEN count(*) > 1 THEN 34 ELSE MAX(S.PosID) END) AS PosID
                ,S.DepID,S.FnYear,S.FnNo, S.SaleEmp
                FROM TSR_Application.dbo.NPT_Sale_Log AS S WITH(NOLOCK)
                INNER JOIN (
                SELECT f.Fortnight_year as FnYear,f.Fortnight_no as FnNo,DepID
                FROM TSR_Application.dbo.view_Fortnight_Table3_ext_DepName2 as f
                WHERE (CONVERT(VARCHAR(10),GETDATE(),121) BETWEEN F.StartDate AND F.finishdate2) AND f.DepID in (1,37,38,40,52)
                ) AS F ON F.DepID = s.DepID AND S.FnYear = F.FnYear AND S.FnNo = F.FnNo
                WHERE S.SaleEmp = '" . $username . "' AND S.SaleStatus != 'R' GROUP BY S.SaleCode,S.DepID,S.FnYear,S.FnNo,S.SaleEmp
                ) AS sl ON sl.SaleEmp = HRM.EmpId
                WHERE HRM.EmpId = ? AND convert(varchar, BirthDay, 112) = ?";
        
        $stmt = $this->db->query($sql, array($username, $password));
        if ($stmt->num_rows() > 0) {
            $result = $stmt->result_array();
            $r          = $result[0];
            $empid      = $r['EmpId'];
            $cardid     = $r['CardID'];
            $firstname  = $r['NameThai'];
            $lastname   = $r['SurName'];
            $divisionid = $r['DivisionId'];
            $status     = $r['StatusId'];
            $resultArr = array(
                'empId'             => $empid,
                'empForWeb'         => str_replace("A","0", $empid),
                'title'             => $r['PreName'],
                'firstname'         => $r['NameThai'],
                'lastname'          => $r['SurName'],
                'cardid'            => $cardid,
                'position'          => $r['PositName'],
                'positionId'        => $r['positid'],
                'department'        => $r['DepartName'],
                'departmentId'      => $r['DepartId'],
                'division'          => $r['DivisionName'],
                'divisionId'        => $divisionid,
                'company'           => $r['CompanyName'],
                'companyId'         => $r['CompanyId'],
                'DistanceCheckIn'   => $r['DistanceCheckIn'],
                'CameraCheckIn'     => $r['CameraCheckIn'],
                'DistanceCheckOut'  => $r['DistanceCheckOut'],
                'CameraCheckOut'    => $r['CameraCheckOut'],
                'branchName'        => $r['BranchName'],
                'branchCode'        => $r['BranchCode'],
                'latitude'          => $r['Latitude'],
                'longitude'         => $r['Longitude'],
                'DistanceIn'        => $r['DistanceIn'],
                'DistanceOut'       => $r['DistanceOut'],
                'status'            => $r['Status'],
                'password'          => $password,
                'SaleCode'          => $r['SaleCode'],
                'TeamNo'            => $r['TeamNo'],
                'TeamCode'          => $r['TeamCode'],
                'TeamNo100'         => substr($r['SaleCode'], 0, 4) . "-" . substr($r['TeamNo'], strlen($r['TeamNo']) - 1, 1),
                'PosID'             => $r['PosID'],
                'DepID'             => $r['DepID'],
                'FnYear'            => $r['FnYear'],
                'FnNo'              => $r['FnNo'],
                'SupTeamList'       => $this->getSupTeam($r['TeamCode'], $r['DepID'], $r['FnYear'], $r['FnNo'])
            );

            $jwtData = array(
                'empId'         => $empid,
                'cardid'        => $cardid,
                'divisionId'    => $divisionid,
                'username'      => $username,
                'password'      => $password,
                'firstname'     => $firstname,
                'lastname'      => $lastname,
                'timestamp'     => time()
            );

            if ($status == 1) {
                $token = $this->jwt->GenerateToken($jwtData, $this->apiKey);
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "Authentication successfull.",
                        "token"     => $token,
                        "data"      => $resultArr
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "รหัสพนักงานนี้อยู่ในสถานะ '" . "ออก" . "'",
                        "token"     => "",
                        "data"      => ""
                    ), 200
                );
            }
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Authentication failed.",
                    "token"     => "",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function authenLDAP($username, $password) {
        $usernameLD = 'it.ldap';
        $passwordLD = '5kDsi@8JO';
        $branch_locator = "thiensurat";

        $branch_locator = "thiensurat";
        $ip_ad          = array("thiensurat"=>"192.168.110.104","thiensurat2"=>"192.168.110.103");
        $dn_base        = 'DC='.$branch_locator.',DC=co,DC=th';
        $dn_host        = $ip_ad[$branch_locator];
        $ldapusers      = $usernameLD.'@'.$branch_locator.'.co.th';
        $ldappasswd     = $passwordLD;

        $ldapconn       = ldap_connect($dn_host, 389) or die("Could not connect to LDAP Server.");
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

        $ldapbind       = ldap_bind($ldapconn ,$ldapusers, $ldappasswd) or die ("Users or Password Invalid.");

        $filter         = "userprincipalname=" . $username . '@' . $branch_locator . '.co.th';
        $justthese      = array("ou", "sn", "givenname", "mail");
        $result         = ldap_search($ldapconn, $dn_base, $filter);
        $entries        = ldap_count_entries($ldapconn, $result);
        $info           = ldap_get_entries($ldapconn, $result);

        for ($i = 0; $i < $info["count"]; $i++){
            $emp_id         = $info[$i]["employeeid"][0];
        }

        $cardid = '';
        $divisionid = '';
        $firstname = '';
        $lastname = '';
        if($info["count"] !=0 ) {
            // $sql    = "SELECT TOP 1 * FROM TSR_Application.dbo.TSR_Full_Employee_Sync_AD WHERE empID like '%$emp_id' ";
            $sql    = "SELECT TOP 1 * FROM TSR_Application.app.TSR_Full_EmployeeLogic2 WHERE EmpId like '%$emp_id' ";
            $value  = $this->Nget($sql);
            $val    = $value[0];

            // $firstname = $r['namethai'];
            // $lastname = $r['surname'];
            // $cardid = $val['cardid'];
            // $divisionid = $val['divisionid'];

            $cardid     = $val['CardID'];
            $firstname  = $val['NameThai'];
            $lastname   = $val['SurName'];
            $divisionid = $val['DivisionId'];
            // $result = array(
            //     'empId'         => $emp_id,
            //     'title'         => $val['prename'],
            //     'firstname'     => $val['namethai'],
            //     'lastname'      => $val['surname'],
            //     'cardid'        => $cardid,
            //     'position'      => $val['PositName'],
            //     'positionId'    => $val['positid'],
            //     'department'    => $val['departname'],
            //     'departmentId'  => $val['departid'],
            //     'division'      => $val['divisionname'],
            //     'divisionId'    => $divisionid,
            //     'company'       => $val['companyname'],
            //     'companyId'     => $val['companyid']
            // );

            $result = array(
                'empId'         => $emp_id,
                'title'         => $val['PreName'],
                'firstname'     => $val['NameThai'],
                'lastname'      => $val['SurName'],
                'cardid'        => $cardid,
                'position'      => $val['PositName'],
                'positionId'    => $val['positid'],
                'department'    => $val['DepartName'],
                'departmentId'  => $val['DepartId'],
                'division'      => $val['DivisionName'],
                'divisionId'    => $divisionid,
                'company'       => $val['CompanyName'],
                'companyId'     => $val['CompanyId']
            );

            $jwtData = array(
                'empId'         => $emp_id,
                'cardid'        => $cardid,
                'divisionId'    => $divisionid,
                'username'      => $username,
                'password'      => $password,
                'firstname'     => $firstname,
                'lastname'      => $lastname,
                'timestamp'     => time()
            );

            $token = $this->jwt->GenerateToken($jwtData, $this->apiKey);

            $this->response(
                array(
                    "status"    => "SUCCESS",
                    "message"   => "Authentication successfull.",
                    "token"     => $token,
                    "data"      => $result
                ), 200
            );
        } else {
            $this->response(
                array(
                    "status"    => "FAILED",
                    "message"   => "Authentication failed.",
                    "token"     => "",
                    "data"      => ""
                ), 200
            );
        }
    }

    public function Nget($query) {
        try{
            $db_host        = 'TSR-SQL02-PRD';
            $db_name        = 'TSR_Application';
            $db_username    = 'tsr_application';
            $db_password    = 'thiens1234';
            $connectionInfo = array("Database"=>$db_name, "UID"=>$db_username, "PWD"=>$db_password, 'CharacterSet' => 'UTF-8', "MultipleActiveResultSets"=>true);
            $conn           = sqlsrv_connect( $db_host, $connectionInfo);

            if( $conn === false ) {
                die( print_r( sqlsrv_errors(), true));
            }

            $result = sqlsrv_query($conn,$query);
            $ans    = array();
            $index  = 0;
            sqlsrv_rows_affected($result );

            if($result == true) {
                while($row = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC)) {
                    $ans[$index]    = $row;
                    $index          = $index + 1;
                }
            }
            sqlsrv_close($conn);
            return $ans;                
        } catch(Exception $e) {
            echo 'Error Message: ' .$e->getMessage();
        }
    }

    public function checkEmpID($username) {
        if (strlen($username) == 6 && substr($username, 0, 1) == "A") {
            return true;
        } else {
            return false;
        }
    }
 
    public function index_put() {
    }
 
    public function index_delete() {
    }

    public function getSupTeam($teamcode, $depid, $fnyear, $fnno) {
        $sql = "SELECT 
                DISTINCT SL.FName, SL.LName, ISNULL(LEFT(SL.SaleCode,4), '-') + ISNULL(cast(SL.TeamNo AS varchar(4)),'-') AS TeamNo,
                CONCAT(CONCAT(LEFT(SL.SaleCode,4), '-'), SL.TeamNo) AS TeamNo100, SL.TeamNo AS Tno 
                FROM SQLUAT.TSR_Application.dbo.NPT_Sale_Log AS SL WITH(NOLOCK) 
                WHERE ISNULL(LEFT(SL.Salecode,4), '-') = ?
                AND SL.PosID = 3 AND SL.TeamNo IS NOT NULL AND SL.SaleStatus != 'R'
                AND SL.DepID = ? AND SL.FnYear = ? AND SL.FnNo = ? ORDER BY Tno ASC";
        $stmt = $this->db->query($sql, array($teamcode, $depid, $fnyear, $fnno));
        if ($stmt->num_rows() > 0) {
            $data = [];
            foreach($stmt->result_array() as $k => $v) {
                $d = array(
                    'No'                => $v['Tno'],
                    'TeamNo'            => $v['TeamNo'],
                    'TeamNo100'         => $v['TeamNo100'],
                    'TeamNo100WithLead' => $v['TeamNo100'] . " : " . $v['FName'] . " " . $v['LName'] . ""
                );

                array_push($data, $d);
            }
            return $data;
        } else {
            return null;
        }

    }
}
<?php 
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');

header("Access-Control-Allow-Origin: *");




class getMain extends REST_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    function index_get() { 

    /*
        $sql = "SELECT top 10000 * 
        FROM TSR_Application.dbo.TSR_Full_EmployeeLogic ";*/

       /* $sql = "SELECT * 
        FROM TSR_Application.dbo.vwHRLogicOrganizationChart";
*/

       /* $sql = "SELECT * 
        FROM TSR_Application.dbo.vwHRLogicOrganizationChart2";*/

        /*$sql = "SELECT * 
        FROM TSR_Application.dbo.vwFullEmployeeLogicComp";*/
/*
$sql = "SELECT * 
FROM TSR_Application.dbo.vwFullEmployeeLogicCompDept";*/

/*
$sql = "SELECT * 
FROM TSR_Application.dbo.vwFullEmployeeLogicCompDeptDiv";*/

/*
$sql = "SELECT  * 
FROM TSR_Application.dbo.TSR_Full_EmployeeLogic2 where empid = 'A61935'";*/

/*
$sql = "SELECT   * 
FROM TSR_Application.dbo.vw_NPT_EmpData_EndLogic where empcode = 'A00062'";
*/


$sql = "select  * 
from TSR_Application.dbo.vw_NPT_SaleNameLogic ";





        $stmt = $this->db->query($sql);
        $result = $stmt->result_array();
        if (count($result) > 0) {
           /* $r = [];
            foreach($result as $k => $v) {
                $rr = array(
                    'value' => trim($v['TeamCode']),
                    'label' => trim($v['TeamName'])
                ); 
                array_push($r, $rr);
            }*/

            $this->response(
                array(
                    'status' 	=> 'SUCCESS',
                    'message' 	=> '',
                    'data' 	    => 1,
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAIL',
                    'message' 	=> 'ไม่สามารถดึงข้อมูลได้',
                    'data' 	    => 2,
                ), REST_Controller::HTTP_OK
            );
        }
  
        
    }

    function getSaleData() {

        //gethostbyaddr($_SERVER['REMOTE_ADDR' ]),

        $sql = "select Team as TeamCode, Replace(Team,' - ','') as TeamName
        from TSR_Application.dbo.vwRptStructM
        WHERE FnYear = 2020 AND DepID not in (3,5) AND len(Team) > 2
        GROUP BY Team";

        $stmt = $this->db->query($sql);
        $result = $stmt->result_array();
        if (count($result) > 0) {
            $r = [];
            foreach($result as $k => $v) {
                $rr = array(
                    'value' => trim($v['TeamCode']),
                    'label' => trim($v['TeamName'])
                ); 
                array_push($r, $rr);
            }

            $this->response(
                array(
                    'status' 	=> 'SUCCESS',
                    'message' 	=> '',
                    'data' 	    => $r,
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAIL',
                    'message' 	=> 'ไม่สามารถดึงข้อมูลได้',
                    'data' 	    => '',
                ), REST_Controller::HTTP_OK
            );
        }
    }


    function getSaleEmpData() {

        $Teamno = $this->input->get('Teamno');
        $Salecode = trim(substr($Teamno,0,4));
        $Teamno = preg_replace('/[^0-9]/', '', $Teamno);

      /*  echo $Salecode; 
        echo $Teamno; exit();*/
  
  
        $sql = "SELECT s.saleemp,s.salecode,LEFT(s.salecode,4)+' - '+CAST(s.TeamNo as VARCHAR) as TeamNo
        ,s.FName + ' ' + s.LName as Fullname,p.PosName
        FROM TSR_Application.dbo.NPT_Sale_Log as s
        LEFT JOIN TSR_Application.dbo.NPT_Position as p on p.posID = s.posID
        INNER JOIN (SELECT f.Fortnight_year as FnYear,f.Fortnight_no as FnNo,DepID
        from TSR_Application.dbo.view_Fortnight_Table3_ext_DepName as f
        WHERE CONVERT(VARCHAR(10),GETDATE(),121) BETWEEN f.StartDate AND f.FinishDate) as f ON f.DepID = s.DepID
        AND s.FnYear = f.FnYear AND s.FnNo = f.FnNo
        where s.salecode like ? AND s.TeamNo = ? AND s.SaleStatus != 'R'";


        $stmt = $this->db->query($sql,array($Salecode.'%',$Teamno)); 
         $result = $stmt->result_array();
        if (count($result) > 0) {
            $r = [];
            $r_check = [];
            foreach($result as $k => $v) {
                $rr = array(
                    'TeamNo' => trim($v['TeamNo']),
                    'salecode' => trim($v['salecode']),
                    'saleval' => trim($v['salecode']).','.trim($v['saleemp']).','.trim($v['Fullname']),
                    'EmpID' => trim($v['saleemp']),
                    'Fullname' => trim($v['Fullname']),
                    'PosName' => trim($v['PosName'])
                ); 

                array_push($r, $rr);


               /* $rr_check = array(
                    trim($v['salecode']) => false
                ); 
                array_push($r_check, $rr_check);*/
                $obj[trim($v['salecode']).','.trim($v['saleemp']).','.trim($v['Fullname'])]= false;
            }


            $this->response(
                array(
                    'status' 	=> 'SUCCESS',
                    'message' 	=> '',
                    'data' 	    => $r,
                    'data_check' => $obj
                ), REST_Controller::HTTP_OK
            );
        } else {
            $this->response(
                array(
                    'status' 	=> 'FAIL',
                    'message' 	=> 'ไม่สามารถดึงข้อมูลได้',
                    'data' 	    => '',
                ), REST_Controller::HTTP_OK
            );
        }
    }


 
    function index_post()
    {
       $this->saveSaleData();
    }


    function saveSaleData() {

       $SaleTeam = trim($this->input->post('SaleTeam'));
       $SaleEmpList = json_decode($this->input->post('SaleEmpList'),true);
       $SaleEmpOutList = json_decode($this->input->post('SaleEmpOutList'),true);


       $SaleEmpList_TRUE = array_filter($SaleEmpList, function ($row) {
            return $row['WorkCheck'] == true;
        });

       $Count_SaleEmp = count($SaleEmpList);
       $Count_SaleEmp_TRUE = count($SaleEmpList_TRUE);
       $Count_SaleEmpOut = count($SaleEmpOutList);
       $Count_AllSale = $Count_SaleEmp+$Count_SaleEmpOut;
       //$Count_WorkSale = $Count_SaleEmp_TRUE+$Count_SaleEmpOut;

       $comname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
       $ipaddress = ($_SERVER['REMOTE_ADDR']);
    
      
       /* Insert Master */
       $sql = "Insert into TSR_DB1.dbo.SaleOne_WorkLog_UAT (TeamCode,TeamNum,TeamWorkNum,TeamOutNum,ipaddress,computername) values (?,?,?,?,?,?)";
       $stmt = $this->db->query($sql,array($SaleTeam,$Count_AllSale,$Count_SaleEmp_TRUE,$Count_SaleEmpOut,$ipaddress,$comname));
       $master_idx =  $this->db->insert_id();

       /* Insert Details SaleEmpList*/
       foreach($SaleEmpList as $key){

            $saledata = explode(",",$key['SaleCode']);
            $salecode =  $saledata[0];
            $empid =  $saledata[1];
            $salename =  $saledata[2];
            $workstatus = $key['WorkCheck'];
   

            $sql = "Insert into TSR_DB1.dbo.SaleOne_WorkLog_Details_UAT (MasterIDX,SaleType,EmpID,EmpName,SaleCode,WorkStatus,ipaddress,computername) values (?,?,?,?,?,?,?,?)";
            $stmt = $this->db->query($sql,array($master_idx,'01',$empid,$salename,$salecode,$workstatus,$ipaddress,$comname));


     }

     /* Insert Details SaleEmpList*/
     foreach($SaleEmpOutList as $key){

        $outsalename = $key['SaleName'];


        $sql = "Insert into TSR_DB1.dbo.SaleOne_WorkLog_Details_UAT (MasterIDX,SaleType,EmpName,WorkStatus,ipaddress,computername) values (?,?,?,?,?,?)";
        $stmt = $this->db->query($sql,array($master_idx,'02',$outsalename,1,$ipaddress,$comname));

 }
     



       $this->response(
           array(
               'status' => 'SUCCESS'
               ,'statuscode' => "100"
               ,'message' => "การทำงานเสร็จสมบูรณ์"
               ,'data' => null
           )
           , REST_Controller::HTTP_OK
       );

   

    }
 
    function index_delete()
    {
        $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST);
        // delete a index and respond with a status/errors
    }
}
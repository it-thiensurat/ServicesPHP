<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');
require(APPPATH.'libraries/REST_Controller.php');
require(APPPATH.'libraries/Format.php');
class AddTds extends REST_Controller
{ 
    public function __construct()
    {
        parent::__construct();
         $this->load->database();
        // $this->db = $this->load->database('uat', TRUE);
    }

    public function index_get() {
        $refno          = $this->input->get('refno');
        $contno         = $this->input->get('contno');
        $contractrefno  = $this->input->get('contractrefno');
        $tdsvalue       = $this->input->get('tdsvalue');
        $tdstypename    = $this->input->get('tdsname');
        $empid          = $this->input->get('empid');
        $date           = date('Y-m-d H:i:s');

        $sql = "SELECT * FROM TSRData_Source.dbo.TSSM_TDS_Data WHERE Refno = ? AND Contno = ?";
        $stmt = $this->db->query($sql, array($refno, $contno));
        if ($stmt->num_rows() > 0) {
            $data = array(
                'TdsTypeName'   => $tdstypename,
                'TdsData'       => $tdsvalue,
            );

            $this->db->where('Refno', $refno);
            $this->db->where('Contno', $contno);
            if ($this->db->update('TSRData_Source.dbo.TSSM_TDS_Data', $data)) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "อัพเดทข้อมูลสำเร็จ",
                        "data"      => $tdsvalue
                    ), 200
                );
            } else {
                $this->response(
                    array(
                        "status"    => "FAILED",
                        "message"   => "ไม่สามารถอัพเดทข้อมูลได้",
                        "data"      => ""
                    ), 200
                );
            }
        } else {
            $data = array(
                'Refno'         => $refno,
                'Contno'        => $contno,
                'ContractRefno' => $contractrefno,
                'TdsTypeName'   => $tdstypename,
                'TdsData'       => $tdsvalue,
                'CreateBy'      => $empid,
                'CreateDate'    => $date
            );

            if ($this->db->insert('TSRData_Source.dbo.TSSM_TDS_Data', $data)) {
                $this->response(
                    array(
                        "status"    => "SUCCESS",
                        "message"   => "บันทึกข้อมูลสำเร็จ",
                        "data"      => $tdsvalue
                    ), 200
                );
            } else {
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

    public function skipButton_get() {
        $this->response(
            array(
                "status"    => "SUCCESS",
                "message"   => "จัดการปุ่มข้าม",
                "data"      => true
            ), 200
        );
    }
}
<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * ข้อมูลสารสนเทศเวชสถิติ
 * - โรคประจำตัวผู้ป่วย
 *
 * @author suchart bunhachirat
 */
class ImcModel extends CI_Model {

    private $Patient = NULL;
    private $ChronicResult = NULL;

    public function __construct() {
        parent::__construct();
        $this->load->database('theptarin');
    }

    public function getPatientData($hn) {
        $my_val = ['hn' => $hn, 'name' => NULL, 'sex' => NULL, 'birthday' => NULL];
        $sql = "SELECT * FROM `patient`WHERE `hn` = ?";
        $query = $this->db->query($sql, [$hn]);
        $row = $query->row();
        if (isset($row)) {

            $my_val['sex'] = ($row->sex == 'M' ) ? 'ชาย' : 'หญิง';
            $my_val['name'] = $row->prefix . $row->fname . " " . $row->lname;
            /**
             * การคำนวนอายุของผู้ป่วย
             * 
             * $birthday_date = date_create_from_format('Y-m-d', $row->birthday_date);
             * $diff = date_diff($birthday_date, date_create_from_format('Y-m-d', $this->visit_date));
             * $th_yyyy = date_format($birthday_date, 'Y') + 543;
             * $patient_name .= " วันเกิด " . date_format($birthday_date, 'd/m/') . $th_yyyy . " ณ วันที่มาอายุ  " . $diff->format('%y ปี %m เดือน %d วัน');
             */
        } else {
            $my_val['name'] = "ไม่พบ HN. " . $this->hn;
        }
        return $this->Patient = $my_val;
    }

    public function getChronicDiag() {
        $sql = "SELECT * FROM `icd10_hn_chronic_list` WHERE `hn`= ?";
        $query = $this->db->query($sql, [$this->Patient['hn']]);
        $my_val = ['hn' => $this->Patient['hn'], 'description' => "\n", 'chronic_count' => $query->num_rows(), 'is_e10' => FALSE, 'is_e11' => FALSE];
        foreach ($query->result_array() as $row) {
            $my_val['description'] .= $row['chronic_code'] . " " . $row['chronic_name'] . "\n";
            $this->ChronicResult[$row['chronic_id']] = $row['chronic_code'] . " " . $row['chronic_name'];
            switch (substr($row['chronic_code'], 0, 3)) {
                case 'E10':
                    $my_val['is_e10'] = TRUE;
                    break;
                case 'E11':
                    $my_val['is_e11'] = TRUE;
                    break;
            }
        }
        return $my_val;
    }

    public function setOpdPrincipalWithChronic($id,$sign_) {
        $chronic_diag=$this->getChronicDiag();
        $sql = "SELECT `principal_id`AS `icd10_code_id` , `hn`AS `icd10_hn` FROM `imc_opd_principal_with_chronic`WHERE `id`= ?";
        $query = $this->db->query($sql, [$id]);
        if ($query->num_rows() > 0 && $this->_isIcd10Hn($sign_)) {
            foreach ($query->result_array() as $row) {
                /**
                 * error ไม่เคยมีรหัสโรคประจำตัวมาก่อน
                 */
                if (array_key_exists($row['icd10_code_id'], $this->ChronicResult)) {
                    $this->db->insert('imc_icd10_hn_chronic', $row);
                }
            }
        }
    }

    public function _isIcd10Hn($sign_) {
        $is = FALSE;
        $sql = "SELECT * FROM `imc_icd10_hn` WHERE `hn`= ?";
        $query = $this->db->query($sql, [$this->Patient['hn']]);
        $row = $query->row();
        if (isset($row)) {
            $is = TRUE;
        } else {
            $is = $this->db->insert('imc_icd10_hn', ['hn' => $this->Patient['hn'], 'sec_owner' => $sign_['user'], 'sec_user' => $sign_['user'],
                'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $sign_['ip_address'], 'sec_script' => $sign_['script']]);
        }
        return $is;
    }

}

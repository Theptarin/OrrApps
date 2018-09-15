<?php

/**
 * ข้อมูลสารสนเทศเวชสถิติ
 * @package OrrApps
 * @author suchart bunhachirat
 */
class ImcModel extends CI_Model {

    /**
     * ข้อมูลผู้ป่วย
     * @var array
     */
    private $Patient = [];

    /**
     * รายการโรคประจำตัว
     * @var array 
     */
    private $ChronicResult = [];

    public function __construct() {
        parent::__construct();
        $this->load->database('theptarin');
    }
    /**
     * รายการข้อมูลแพทย์
     * @return array 
     */
    public function getDoctorList() {
        $query = $this->db->query('SELECT * FROM `doctor`');
        $list = [];
        foreach ($query->result_array() as $row) {
            $list[$row['doctor_id']] = $row['doctor_id'] . " : " . $row['doctor_name'];
        }
        return $list;
    }

    /**
     * ข้อมูลผู้ป่วย
     * @param integer $hn รหัสประจำตัวผู้ป่วย
     * @return array [hn,name,sex,birthday]
     */
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

    /**
     * ข้อมูลโรคประจำตัว/เรื้อรัง
     * @return array [hn,description,chronic_count,is_e10,is_e11]
     */
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

    /**
     * บันทึกข้อมูลโรคประจำตัวผู้ป่วยเมื่อบันทึกรหัสวินิจฉัยโรคผู้ป่วยนอก
     * @param int $id รหัสรายการที่เพิ่มล่าสุด
     * @param array $sign_ ข้อมูลการใช้งานระบบ
     */
    public function setOpdPrincipalWithChronic($id, $sign_) {
        $sql = "SELECT `principal_id`AS `icd10_code_id` , `hn`AS `icd10_hn` FROM `imc_opd_principal_with_chronic`WHERE `id`= ?";
        $query = $this->db->query($sql, [$id]);
        if ($query->num_rows() > 0 && $this->_isIcd10Hn($sign_)) {
            foreach ($query->result_array() as $row) {
                if (!array_key_exists($row['icd10_code_id'], $this->ChronicResult)) {
                    $this->db->insert('imc_icd10_hn_chronic', $row);
                }
            }
        }
    }

    /**
     * ตรวจสอบข้อมูลโรคประจำตัวผู้ป่วย
     * @param type $sign_
     * @return boolean คืนค่าจริงเมื่อการตรวจสอบไม่พบความผิดปกติ
     */
    private function _isIcd10Hn($sign_) {
        $sql = "SELECT * FROM `imc_icd10_hn` WHERE `hn`= ?";
        $query = $this->db->query($sql, [$this->Patient['hn']]);
        $row = $query->row();
        if (isset($row)) {
            $is = TRUE;
        } else {
            $is = $this->db->insert('imc_icd10_hn', ['hn' => $this->Patient['hn'], 'sec_owner' => $sign_['user'], 'sec_user' => $sign_['user'],
                'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $sign_['ip_address'], 'sec_script' => $sign_['script']]);
        }
        if (!$is) {
            die('รายการข้อมูลโรคประจำตัวผู้ป่วยไม่ถูกต้อง กรุณาแจ้งผู้ดูแล');
        }
        return $is;
    }

}

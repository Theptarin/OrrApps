<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Icd10
 *
 * @author it
 */
class IMC extends MY_Controller {

    private $acrud = NULL;
    private $hn = NULL;
    private $vn = NULL;
    private $visit_date = NULL;
    private $doctor_id = NULL;
    public $opd = NULL;

    public function __construct() {
        parent::__construct();
        $group = "theptarin";
        $db = $this->getDbData($group);
        $this->acrud = new OrrACRUD($db, $group);
        $this->setACRUD($this->acrud);
    }

    public function icd10_code() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_code')->fieldType('chronic', 'checkbox_boolean')->fieldType('external_cause', 'checkbox_boolean');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_hn() {
        $crud = $this->acrud;
        $fields = ['hn', 'chronic_diag'];
        $crud->setTable('imc_icd10_hn')->fields($fields)->columns($fields);
        $crud->setRelationNtoN('chronic_diag', 'imc_icd10_hn_chronic', 'imc_icd10_code', 'icd10_hn', 'icd10_code_id', '{code} {name_en}', 'code', ['chronic' => '1']);
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_opd() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->unsetAdd()->setRead();
        $crud->setRelationNtoN('opd_principal_diag', 'imc_icd10_opd_principal', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');

        if ($crud->getState() === 'Initial') {
            $crud->fieldType('signature_opd', 'dropdown_search', $this->_getDoctor());
        }

        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_opd_add($dd = "", $mm = "", $yyyy = "", $vn = "", $hn = "", $doctor_id = "") {
        $this->hn = $hn;
        $this->visit_date = $yyyy . "-" . $mm . "-" . $dd;
        $this->vn = $vn;
        $this->doctor_id = $doctor_id;
        $th_yyyy = $yyyy + 543;
        $patient_name = $this->_getPatientName();
        $chronic_diag = $this->_getChronicDiag();
        $this->description = "วันที่ $dd/$mm/$th_yyyy VN. $vn HN. $hn " . $patient_name . " " . " แพทย์ " . $this->_getDoctorName() . " " . $chronic_diag['diag_list'];
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->where(['hn' => $hn])->columns(['visit_date', 'description', 'signature_opd'])->unsetEdit()->setRead()
                ->requiredFields(['description', 'principal_diag'])->addFields(['description', 'principal_diag', 'external_cause'])->setSubject('ข้อมูลวินิจฉัยโรค ' . $patient_name);
        $crud->setRelationNtoN('principal_diag', 'imc_icd10_opd_principal', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', $chronic_diag['sql_where']);
        $crud->setRelationNtoN('external_cause', 'imc_icd10_opd_external', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', ['external_cause' => '1']);
        /**
         * Default value add form
         */
        $crud->callbackAddForm(function ($data) {
            $data['description'] = $this->description;
            return $data;
        });
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_ipd() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_ipd');
        $crud->columns(['discharge_date', 'an', 'hn']);
        $crud->setRelationNtoN('ipd_principal_diag', 'imc_ipd_principal_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        $crud->setRelationNtoN('ipd_comorbidity_diag', 'imc_ipd_comorbidity_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        $crud->setRelationNtoN('ipd_complication_diag', 'imc_ipd_complication_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        //$crud->setRelationNtoN('ipd_consultation','imc_ipd_consultation','ttr_hims.doctor_name','icd10_ipd_id','doctor_name_id','{fname} {lname} [ {doctor_id} ]');
        $crud->setRelationNtoN('ipd_other_diag', 'imc_ipd_other_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function opd_visit() {
        $crud = $this->acrud;
        $crud->setTable('imc_opd_visit')->setPrimaryKey('hn', 'imc_opd_visit');
        $fields = $this->getAllFields();
        $crud->columns($fields)->fields($fields);
        //$crud->unsetAdd()->unsetEdit()->unsetDelete();
        $crud->unsetOperations();
        $crud->setActionButton('ICD10', 'fa fa-user', function ($row) {
            return 'icd10_opd_add/' . $row->visit_date . '/' . $row->vn . '/' . $row->hn . '/' . $row->doctor_id . '#/add';
        }, true);
        $output = $crud->render();
        $this->setMyView($output);
    }

    private function _getDoctor() {
        $this->load->database('theptarin');
        $query = $this->db->query('SELECT * FROM `doctor`');
        //$rows[] = NULL;
        foreach ($query->result() as $row) {
            $rows[$row->doctor_id] = $row->doctor_id . " : " . $row->doctor_name . " : " . $row->category;
        }
        return $rows;
    }

    private function _getDoctorName() {
        $this->load->database('theptarin');
        $sql = "SELECT * FROM `doctor` WHERE `doctor_id` = ?";
        $query = $this->db->query($sql, [$this->doctor_id]);
        $row = $query->row();
        if (isset($row)) {
            $doctor_name = $row->doctor_id . " " . $row->doctor_name;
        } else {
            $doctor_name = "ไม่มีรหัสแพทย " . $row->doctor_id;
        }
        return $doctor_name;
    }

    private function _getPatientName() {
        $this->load->database('theptarin');
        $sql = "SELECT * FROM `patient`WHERE `hn` = ?";
        $query = $this->db->query($sql, [$this->hn]);
        $row = $query->row();
        if (isset($row)) {
            $sex = ($row->sex == 'M' ) ? 'ชาย' : 'หญิง';
            $patient_name = $row->prefix . $row->fname . " " . $row->lname . " เพศ " . $sex;
            /**
             * การคำนวนอายุของผู้ป่วย
             * 
             * $birthday_date = date_create_from_format('Y-m-d', $row->birthday_date);
             * $diff = date_diff($birthday_date, date_create_from_format('Y-m-d', $this->visit_date));
             * $th_yyyy = date_format($birthday_date, 'Y') + 543;
             * $patient_name .= " วันเกิด " . date_format($birthday_date, 'd/m/') . $th_yyyy . " ณ วันที่มาอายุ  " . $diff->format('%y ปี %m เดือน %d วัน');
             */
        } else {
            $patient_name = "ไม่พบ HN. " . $this->hn;
        }
        return $patient_name;
    }

    /**
     * คืนข้อมูลโรคประจำตัวผู้ป่วย และกำหนดค่าที่เกี่ยวข้องอื่นๆ
     * @return string
     */
    private function _getChronicDiag() {
        $this->load->database('theptarin');
        $sql = "SELECT * FROM `icd10_hn_chronic_list` WHERE `hn`= ?";
        $query = $this->db->query($sql, [$this->hn]);
        $my_val['diag_list'] = "\n";
        
        foreach ($query->result_array() as $row) {
            $my_val['diag_list'] .= $row['chronic_code'] . " " . $row['chronic_name'] . "\n";
            $diabetes_code = (substr($row['chronic_code'], 0, 3) );
            switch (substr($row['chronic_code'], 0, 3)) {
                case 'E10':
                    $my_val['sql_where'] = ['external_cause' => '0', 'code NOT LIKE ?' => 'E11%'];
                    break;
                case 'E11':
                    $my_val['sql_where'] = ['external_cause' => '0', 'code NOT LIKE ?' => 'E10%'];
                    break;
                default:
                    $my_val['sql_where'] = ['external_cause' => '0'];
                    break;
            }
        }
        return $my_val;
    }

    public function eventBeforeInsert($val_) {
        switch ($this->OrrACRUD->getTable()) {
            case 'imc_icd10_opd':
                $val_->data['visit_date'] = $this->visit_date;
                $val_->data['vn'] = $this->vn;
                $val_->data['hn'] = $this->hn;
                $val_->data['signature_opd'] = $this->doctor_id;
                break;

            default:
                break;
        }
        return parent::eventBeforeInsert($val_);
    }

    public function eventAfterInsert($val_) {
        switch ($this->OrrACRUD->getTable()) {
            case 'imc_icd10_opd':
                $this->_setOpdPrincipalWithChronic($val_->insertId);
                break;

            default:
                break;
        }
        return parent::eventAfterInsert($val_);
    }

    private function _setOpdPrincipalWithChronic($id) {
        $this->load->database('theptarin');
        $sql = "SELECT `principal_id`AS `icd10_code_id` , `hn`AS `icd10_hn` FROM `imc_opd_principal_with_chronic`WHERE `id`= ?";
        $query = $this->db->query($sql, [$id]);
        if ($query->num_rows() > 0 && $this->_isIcd10Hn()) {
            foreach ($query->result_array() as $row) {
                //Error Duplicate entry
                $this->db->insert('imc_icd10_hn_chronic', $row);
            }
        }
    }

    private function _isIcd10Hn() {
        $status = FALSE;
        $this->load->database('theptarin');
        $sql = "SELECT * FROM `imc_icd10_hn` WHERE `hn`= ?";
        $query = $this->db->query($sql, [$this->hn]);
        $row = $query->row();
        if (isset($row)) {
            $status = TRUE;
        } else {
            $sign_ = $this->acrud->getSignData();
            $status = $this->db->insert('imc_icd10_hn', ['hn' => $this->hn, 'sec_owner' => $sign_['user'], 'sec_user' => $sign_['user'],
                'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $sign_['ip_address'], 'sec_script' => $sign_['script']]);
        }
        return $status;
    }

}

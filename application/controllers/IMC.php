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
    private $visitDate = NULL;
    private $doctorId = NULL;
    private $dischargeDate = NULL;
    private $an = NULL;
    private $isDoctorId = FALSE;

    public function __construct() {
        parent::__construct();
        $group = "theptarin";
        $db = $this->getDbData($group);
        $this->acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->acrud);
        $this->load->model('ImcModel');
    }

    /**
     * ทะเบียนรหัสการวินิจฉัยโรค
     */
    public function icd10Code() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_code')->fieldType('chronic', 'checkbox_boolean')->fieldType('external_cause', 'checkbox_boolean')
                ->setRelation('icd10_group_code', 'imc_icd10_group', 'name');
        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * ทะเบียนกลุ่มการวินิจฉัยโรค
     */
    public function icd10Group() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_group');
        $fields = $this->getAllFields();
        $crud->columns($fields)->fields($fields);
        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * ทะเบียนโรคประจำตัว
     */
    public function icd10Hn() {
        $crud = $this->acrud;
        $fields = ['hn', 'chronic_diag'];
        $crud->setTable('imc_icd10_hn')->fields($fields)->columns($fields);
        $crud->setRelationNtoN('chronic_diag', 'imc_icd10_hn_chronic', 'imc_icd10_code', 'icd10_hn', 'icd10_code_id', '{code} {name_en}', 'code', ['chronic' => '1']);
        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * ข้อมูลการวินิจฉัยโรคผู้ป่วยนอก
     */
    public function icd10Opd() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->unsetAdd()->setRead();
        $crud->setRelationNtoN('opd_principal_diag', 'imc_icd10_opd_principal', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');
        $crud->setRelationNtoN('opd_external_cause', 'imc_icd10_opd_external', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', ['external_cause' => '1']);

        if ($crud->getState() === 'Initial') {
            $crud->fieldType('signature_opd', 'dropdown_search', $this->_getDoctor());
        }

        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * หน้าจอข้อมูลงานบริการผู้ป่วยนอก
     */
    public function opdVisit() {
        $crud = $this->acrud;
        $crud->setTable('imc_opd_visit')->setPrimaryKey('hn', 'imc_opd_visit');
        $fields = $this->getAllFields();
        $crud->columns($fields)->fields($fields)->unsetOperations();
        $crud->setActionButton('ICD10', 'fa fa-user', function ($row) {
            if ($row->icd10_opd_id == "") {
                return 'icd10OpdAdd/' . $row->visit_date . '/' . $row->vn . '/' . $row->hn . '/' . $row->doctor_id . '#/add';
            } else {
                return 'icd10OpdEdit/' . $row->hn . '#/';
            }
        }, true);
        if ($crud->getState() === 'Initial') {
            $crud->fieldType('doctor_id', 'dropdown_search', $this->ImcModel->getDoctorList());
        }

        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * หน้าจอเพิ่มข้อมูลการวินิจฉัยโรคผู้ป่วยนอก
     * @param type $dd
     * @param type $mm
     * @param type $yyyy
     * @param type $vn
     * @param type $hn
     * @param type $doctor_id
     */
    public function icd10OpdAdd($dd = "", $mm = "", $yyyy = "", $vn = "", $hn = "", $doctor_id = "") {
        $this->load->model('ImcModel');
        $this->hn = $hn;
        $this->visitDate = $yyyy . "-" . $mm . "-" . $dd;
        $this->vn = $vn;
        $this->doctorId = $doctor_id;
        $th_yyyy = $yyyy + 543;
        $patient_data = $this->ImcModel->getPatientData($hn);
        $chronic_diag = $this->ImcModel->getChronicDiag();
        $this->description = "วันที่ $dd/$mm/$th_yyyy VN. $vn HN. $hn " . $patient_data['name'] . " เพศ " . $patient_data['sex'] . " แพทย์ " . $this->_getDoctorName() . " " . $chronic_diag['description'];
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->where(['hn' => $hn])->columns(['visit_date', 'description', 'signature_opd'])->setRead()
                ->requiredFields(['description', 'principal_diag'])->addFields(['description', 'principal_diag', 'external_cause'])->setSubject('ข้อมูลวินิจฉัยโรค HN. ' . $patient_data['hn'] . " " . $patient_data['name']);
        $crud->setRelationNtoN('principal_diag', 'imc_icd10_opd_principal', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', $this->_getPrincipalWhereSQL($chronic_diag));
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

    public function icd10OpdEdit($hn) {
        /**
         * ตรวจสอบข้อมูลโรคเรื้อรังเมื่อมีการแก้ไข
         * $patient_data = $this->ImcModel->getPatientData($hn);
         *  $chronic_diag = $this->ImcModel->getChronicDiag();
         */
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->where(['hn' => $hn])->unsetOperations();
        $crud->setRelationNtoN('principal_diag', 'imc_icd10_opd_principal', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', $this->_getPrincipalWhereSQL($chronic_diag));
        $crud->setRelationNtoN('external_cause', 'imc_icd10_opd_external', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}', 'code', ['external_cause' => '1']);
        $output = $crud->render();
        $this->setMyView($output);
    }

    private function _getPrincipalWhereSQL($chronic_diag) {
        if ($chronic_diag['is_e10'] && $chronic_diag['is_e11']) {
            $message = "HN. " . $chronic_diag['hn'] . " พบว่ามีรหัส E10 E11 ในข้อมูลโรคประจำตัว [ " . $chronic_diag['description'] . " ]";
            die($message);
        } else if ($chronic_diag['is_e10']) {
            $my_val = ['external_cause' => '0', 'code NOT LIKE ?' => 'E11%'];
        } elseif ($chronic_diag['is_e11']) {
            $my_val = ['external_cause' => '0', 'code NOT LIKE ?' => 'E10%'];
        } else {
            $my_val = ['external_cause' => '0'];
        }
        return $my_val;
    }

    /**
     * หน้าจอข้อมูลผู้ป่วยในที่เคยพักรักษา
     */
    public function ipdDischarge() {
        $crud = $this->acrud;
        $crud->setTable('imc_ipd_discharge')->setPrimaryKey('an', 'imc_ipd_discharge');
        $fields = $this->getAllFields();
        $crud->columns($fields)->fields($fields)->unsetOperations();
        $crud->setActionButton('ICD10', 'fa fa-user', function ($row) {
            return 'icd10IpdAdd/' . $row->discharge_date . '/' . $row->an . '/' . $row->hn . '#/add';
        }, true);
        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * 
     * @param type $dd
     * @param type $mm
     * @param type $yyyy
     * @param type $an
     * @param type $hn
     */
    public function icd10IpdAdd($dd = "", $mm = "", $yyyy = "", $an = "", $hn = "") {
        $this->load->model('ImcModel');
        $this->dischargeDate = $yyyy . "-" . $mm . "-" . $dd;
        $this->an = $an;
        $this->hn = $hn;
        $th_yyyy = $yyyy + 543;
        $patient_data = $this->ImcModel->getPatientData($hn);
        $chronic_diag = $this->ImcModel->getChronicDiag();
        $this->description = "วันที่ $dd/$mm/$th_yyyy AN. $an HN. $hn " . $patient_data['name'] . " เพศ " . $patient_data['sex'] . " " . $chronic_diag['description'];
        $crud = $this->acrud;
        //$crud->setTable('imc_icd10_ipd')->where(['an' => $an])->columns(['discharge_date', 'description', 'an'])->setRead()
        //        ->requiredFields(['description', 'principal_diag'])->addFields(['description', 'principal_diag', 'external_cause'])->setSubject('ข้อมูลวินิจฉัยโรค HN. ' . $patient_data['hn'] . " " . $patient_data['name']);
        $crud->setTable('imc_icd10_ipd')->where(['an' => $an])->columns(['discharge_date', 'description', 'an'])->setRead()
                ->addFields(['description', 'ipd_principal_diag', 'clinical_summary', 'signature_ipd'])
                ->setSubject('ข้อมูลวินิจฉัยโรค HN. ' . $patient_data['hn'] . " " . $patient_data['name']);
        $crud->setRelationNtoN('ipd_principal_diag', 'imc_ipd_principal_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}', 'code', $this->_getPrincipalWhereSQL($chronic_diag));

        if ($crud->getState() === 'Initial') {
            $doctor_ = $this->_getDoctor();
            $doctor_[''] = "ระบุแพทย์";
            $crud->fieldType('signature_ipd', 'dropdown_search', $doctor_);
        }
        //$crud->setRelationNtoN('ipd_comorbidity_diag', 'imc_ipd_comorbidity_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        //$crud->setRelationNtoN('ipd_complication_diag', 'imc_ipd_complication_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
        //$crud->setRelationNtoN('ipd_consultation','imc_ipd_consultation','ttr_hims.doctor_name','icd10_ipd_id','doctor_name_id','{fname} {lname} [ {doctor_id} ]');
        //$crud->setRelationNtoN('ipd_other_diag', 'imc_ipd_other_diag', 'imc_icd10_code', 'icd10_ipd_id', 'icd10_code_id', '{code} {name_en}');
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

    public function icd10Ipd() {
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
        $query = $this->db->query($sql, [$this->doctorId]);
        $row = $query->row();
        if (isset($row)) {
            $doctor_name = $row->doctor_id . " " . $row->doctor_name;
            $this->isDoctorId = TRUE;
        } else {
            $doctor_name = "ไม่มีข้อมูลรหัสแพทย์ " . $this->doctorId;
            $this->isDoctorId = FALSE;
        }
        return $doctor_name;
    }

    public function eventBeforeInsert($val_) {
        switch ($this->acrud->getTable()) {
            case 'imc_icd10_opd':
                if (!$this->isDoctorId) {
                    $this->setMyJsonMessageFailure('รหัสแพทย์ไม่ถูกต้อง');
                }
                $val_->data['visit_date'] = $this->visitDate;
                $val_->data['vn'] = $this->vn;
                $val_->data['hn'] = $this->hn;
                $val_->data['signature_opd'] = $this->doctorId;
                break;

            case 'imc_icd10_ipd':
                $val_->data['discharge_date'] = $this->dischargeDate;
                $val_->data['an'] = $this->an;
                $val_->data['hn'] = $this->hn;
                break;

            default:
                break;
        }
        return parent::eventBeforeInsert($val_);
    }

    public function eventAfterInsert($val_) {
        switch ($this->acrud->getTable()) {
            case 'imc_icd10_opd':
                $this->ImcModel->setOpdPrincipalWithChronic($val_->insertId, $this->acrud->getSignData());
                break;

            default:
                break;
        }
        return parent::eventAfterInsert($val_);
    }

}

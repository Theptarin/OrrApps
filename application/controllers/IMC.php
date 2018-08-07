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
        $crud->setTable('imc_icd10_code');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_opd($dd = "", $mm = "", $yyyy = "", $vn = "", $hn = "", $doctor_id = "") {
        $this->opd['hn'] = $hn;
        $this->opd['visit_date'] = $yyyy . "-" . $mm . "-" . $dd;
        $this->opd['vn'] = $vn;
        $this->opd['doctor_id'] = $doctor_id;
        $this->opd['description'] = "รายละเอียดข้อมูล";
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->where(['hn' => $hn])->columns(['visit_date', 'vn', 'hn', 'signature_opd'])
                ->addFields(['description', 'opd_principal_diag', 'external_cause']);
        $crud->setRelationNtoN('opd_principal_diag', 'imc_opd_principal_diag', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');

        if ($crud->getState() === 'Initial') {
            $crud->fieldType('signature_opd', 'dropdown_search', $this->_getDoctor());
        }
        /**
         * Default value add form
         */
        $crud->callbackAddForm(function ($data) {
            $data['description'] = $this->opd['description'];
            return $data;
        });
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_opd_add($dd = "", $mm = "", $yyyy = "", $vn = "", $hn = "", $doctor_id = "") {
        $this->opd['hn'] = $hn;
        $this->opd['visit_date'] = $yyyy . "-" . $mm . "-" . $dd;
        $this->opd['vn'] = $vn;
        $this->opd['doctor_id'] = $doctor_id;
        $th_yyyy = $yyyy + 543;
        $this->opd['description'] = "$dd/$mm/$th_yyyy  VN. $vn HN. " . $this->_getPatient();
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd')->where(['hn' => $hn])->columns(['hn', 'description'])->unsetEdit()
                ->setRead()->addFields(['description', 'opd_principal_diag', 'external_cause']);
        $crud->setRelationNtoN('opd_principal_diag', 'imc_opd_principal_diag', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');
        /**
         * Default value add form
         */
        $crud->callbackAddForm(function ($data) {
            $data['description'] = $this->opd['description'];
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
        $rows[] = NULL;
        foreach ($query->result() as $row) {
            $rows[$row->doctor_id] = $row->doctor_id . " : " . $row->doctor_name . " : " . $row->category;
        }
        return $rows;
    }

    private function _getPatient() {
        $this->load->database('theptarin');
        $sql = "SELECT * FROM `patient`WHERE `hn` = ?";
        $query = $this->db->query($sql, [$this->opd['hn']]);
        $row = $query->row();
        if (isset($row)) {
            $sex = ($row->sex == 'M' ) ? 'ชาย' : 'หญิง';
            $patient = $row->hn . " " . $row->prefix . $row->fname . " " . $row->lname . " เพศ " . $sex;
            $birthday_date = date_create_from_format('Y-m-d', $row->birthday_date);
            $diff = date_diff($birthday_date, date_create_from_format('Y-m-d', $this->opd['visit_date']));
            $th_yyyy = date_format($birthday_date, 'Y') + 543;
            $patient .= " วันเกิด " . date_format($birthday_date, 'd/m/') . $th_yyyy . " ณ วันที่มาอายุ  " . $diff->format('%y ปี %m เดือน %d วัน');
        } else {
            $patient = "ไม่พบ HN. " . $this->opd['hn'];
        }
        return $patient;
    }

    public function eventBeforeInsert($val_) {
        switch ($this->OrrACRUD->getTable()) {
            case 'imc_icd10_opd':
                $val_->data['visit_date'] = $this->opd['visit_date'];
                $val_->data['vn'] = $this->opd['vn'];
                $val_->data['hn'] = $this->opd['hn'];
                $val_->data['signature_opd'] = $this->opd['doctor_id'];
                break;

            default:
                break;
        }
        return parent::eventBeforeInsert($val_);
    }

}

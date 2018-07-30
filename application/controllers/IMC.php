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

    public function icd10_opd() {
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd');
        //$crud->where(['hn' => 0]);
        $crud->columns(['visit_date', 'vn', 'hn', 'signature_opd']);
        $crud->setRelationNtoN('opd_principal_diag', 'imc_opd_principal_diag', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');
        //print_r($this->_getDoctorName());
        if ($crud->getState() === 'Initial') {
            //$crud->fieldType('hn', 'dropdown_search', $this->_getPatient());
            $crud->fieldType('signature_opd', 'dropdown_search', $this->_getDoctor());
        }

        //$crud->setRelation('signature_opd', 'ttr_hims.doctor_name', '{fname} {lname} [ {doctor_id} ]');
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
        $crud->setActionButton('Avatar', 'fa fa-user', function ($row) {
            return 'icd10_opd#/add';
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
        $query = $this->db->query('SELECT * FROM `patient`');
        $rows[] = NULL;
        foreach ($query->result() as $row) {
            $rows[$row->hn] = $row->hn . " : " . $row->fname . " " . $row->lname;
        }
        return $rows;
    }

}

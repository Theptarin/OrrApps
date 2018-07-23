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
    
    public function icd10_code(){
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_code');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function icd10_opd(){
        $crud = $this->acrud;
        $crud->setTable('imc_icd10_opd');
        $crud->columns(['visit_date', 'vn', 'hn','opd_principal_diag']);
        $crud->setRelationNtoN('opd_principal_diag','imc_opd_principal_diag','imc_icd10_code','icd10_opd_id','icd10_code_id','{code} {name_en}');
        //$crud->setRelation('signature_opd', 'ttr_hims.doctor_name', '{fname} {lname} [ {doctor_id} ]');
        $output = $crud->render();
        $this->setMyView($output);
    }
}

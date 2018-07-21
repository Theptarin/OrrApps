<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

class Crud extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    private function _getMyDb() {
        $db = [];
        include(APPPATH . 'config/database.php');
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
                'host' => $db['theptarin']['hostname'],
                'database' => $db['theptarin']['database'],
                'username' => $db['theptarin']['username'],
                'password' => $db['theptarin']['password'],
                'charset' => 'utf8'
            ]
        ];
    }

    private function _getGroceryCrudEnterprise() {
        //$db = $this->_getDbData();
        $db = $this->_getMyDb();
        $config = include(APPPATH . 'config/gcrud-enterprise.php');
        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }

    public function icd10_opd() {
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('imc_icd10_opd');
        $crud->columns(['visit_date', 'vn', 'hn', 'opd_principal_diag']);
        $crud->setRelationNtoN('opd_principal_diag', 'imc_opd_principal_diag', 'imc_icd10_code', 'icd10_opd_id', 'icd10_code_id', '{code} {name_en}');
        //$crud->setRelation('signature_opd', 'ttr_hims.doctor_name', '{fname} {lname} [ {doctor_id} ]');
        $output = $crud->render();
        $this->_example_output($output);
    }

    function _example_output($output = null) {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        $this->load->view('example.php', $output);
    }

}

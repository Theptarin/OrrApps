<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Example
 *
 * @author it
 */
class Example extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    private function _getDbData() {
        $db = [];
        include(APPPATH . 'config/database.php');
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
                'host' => $db['default']['hostname'],
                'database' => $db['default']['database'],
                'username' => $db['default']['username'],
                'password' => $db['default']['password'],
                'charset' => 'utf8'
            ]
        ];
    }

    private function _getGroceryCrudEnterprise() {
        $db = $this->_getDbData();
        $config = include(APPPATH . 'config/gcrud-enterprise.php');
        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }

    public function demo_set_model() {
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('customers');
        $crud->setSubject('Customer', 'Customers');
        $crud->columns(['customerName', 'country', 'state', 'addressLine1']);
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

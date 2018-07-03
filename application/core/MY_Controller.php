<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

/**
 * Description of MY_Controller
 *
 * @author it
 */
class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

     public function _getDbData($group) {
        $db = [];
        include(APPPATH . 'config/database.php');
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
                'host' => $db[$group]['hostname'],
                'database' => $db[$group]['database'],
                'username' => $db[$group]['username'],
                'password' => $db[$group]['password'],
                'charset' => 'utf8'
            ]
        ];
    }


    public function _getGroceryCrudEnterprise($group='default') {
        $db = $this->_getDbData($group);
        $config = include(APPPATH . 'config/gcrud-enterprise.php');
        $groceryCrud = new GroceryCrud($config, $db);
        return $groceryCrud;
    }

    protected function _example_output($output = null) {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        $this->load->view('example.php', $output);
    }

}

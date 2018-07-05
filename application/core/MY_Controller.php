<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once(APPPATH . 'libraries/Orr_ACRUD.php');

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
    
    public function index(){
        //echo"MyIndex";
        $this->setMyView((object) array('output' => '', 'js_files' => array(), 'css_files' => array()));
    }

    public function getDbData($group) {
        $db = [];
        require_once(APPPATH . 'config/database.php');
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


    public function getOrrACRUD($group='default') {
        $db = $this->getDbData($group);
        $config = include(APPPATH . 'config/gcrud-enterprise.php');
        $OrrACRUD = new Orr_ACRUD($config, $db);
        return $OrrACRUD;
    }

    protected function setMyView($output = null,$view="Project_") {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        $this->load->view($view, $output);
    }

}

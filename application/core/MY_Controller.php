<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once(APPPATH . 'libraries/OrrACRUD.php');

/**
 * Description of MY_Controller
 *
 * @author it
 */
class MY_Controller extends CI_Controller {

    protected $OrrACRUD = null;

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    /**
     * Default View of project
     */
    public function index() {
        $ci_uri = new CI_URI();
        $this->setMyView((object) array('output' => '', 'js_files' => array(), 'css_files' => array()), $ci_uri->segment(1) . '_');
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

    public function setACRUD(OrrACRUD $acrud){
        $this->getACRUD($acrud);
        return $this;
    }

    public function getACRUD(OrrACRUD $acrud) {
        $acrud->callbackBeforeInsert(array($this, 'eventBeforeInsert'))
                ->callbackAfterInsert(array($this, 'eventAfterInsert'))
                ->callbackBeforeUpdate(array($this, 'eventBeforeUpdate'))
                ->callbackAfterUpdate(array($this, 'eventAfterUpdate'))
                ->callbackAfterDelete(array($this, 'eventAfterDelete'))
                ->callbackAfterDeleteMultiple(array($this, 'eventAfterDeleteMultiple'));
        return $this->OrrACRUD = $acrud;
    }

    public function getAllFields() {
        return $this->OrrACRUD->getAllFields();
    }

    protected function setMyView($output = null, $view = "Project_") {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        if (is_object($this->OrrACRUD)) {
            $sign_ = $this->OrrACRUD->getSignData();
            $view = $sign_['project'];
        }

        $this->load->view($view, $output);
    }

    public function eventBeforeInsert($val_) {
        $sign_ = $this->OrrACRUD->getSignData();
        $val_->data['sec_owner'] = $sign_['user'];
        $val_->data['sec_user'] = $sign_['user'];
        $val_->data['sec_time'] = date("Y-m-d H:i:s");
        $val_->data['sec_ip'] = $sign_['ip_address'];
        $val_->data['sec_script'] = $sign_['script'];
        return $val_;
    }

    public function eventAfterInsert($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterInsert');
        return $val_;
    }

    public function eventBeforeUpdate($val_) {
        $sign_ = $this->OrrACRUD->getSignData();
        $val_->data['sec_user'] = $sign_['user'];
        $val_->data['sec_time'] = date("Y-m-d H:i:s");
        $val_->data['sec_ip'] = $sign_['ip_address'];
        $val_->data['sec_script'] = $sign_['script'];
        return $val_;
    }

    public function eventAfterUpdate($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterUpdate');
        return $val_;
    }

    public function eventAfterDelete($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterDelete');
        return $val_;
    }

    public function eventAfterDeleteMultiple($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterDeleteMultiple');
        return $val_;
    }

    protected function addActivityPostLog($EV_log, $EV_name) {
        $this->OrrACRUD->AddActivity($EV_name . ' : ' . $EV_log);
    }

}

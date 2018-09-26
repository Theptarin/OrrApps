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

    protected $OrrACRUD = NULL;
    protected $Sign_ = [];
    protected $View_ = [];
    protected $Orr_ = ['title' => "Orr Projects"];

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    /**
     * Default View of project
     */
    public function index() {
        //$ci_uri = new CI_URI();
        $this->setMyView((object) ['output' => '', 'js_files' => [], 'css_files' => []]);
        //$this->setMyView((object) ['output' => '']);
    }

    protected function getDbData($group) {
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

    protected function setACRUD(OrrACRUD $acrud) {
        $this->Sign_ = $acrud->getSignData();
        if ($this->Sign_['status'] === 'Online') {
            $this->eventState($acrud->getState());
            $this->getACRUD($acrud);
            return $this;
        } else {
            $sign_url = $acrud->getSignUrl();
            $message = "สถานะการเข้าใช้งานไม่ออนไลน์ ในขณะนี้ ";
            $message .= "<a href=\"$sign_url\"> คลิกที่นี่เพื่อบันทึกเข้าใช้งาน </a>";
            if ($acrud->getState() === 'Main') {
                die($message);
            } else {
                $this->setMyJsonMessageFailure($message);
            }
        }
    }

    protected function getACRUD(OrrACRUD $acrud) {
        $acrud->unsetBootstrap()->unsetJquery()->unsetJqueryUi();
        $acrud->setSubject('รายการใหม่', $this->Sign_['form_title']);
        $acrud->callbackBeforeInsert(array($this, 'eventBeforeInsert'))
                ->callbackAfterInsert(array($this, 'eventAfterInsert'))
                ->callbackBeforeUpdate(array($this, 'eventBeforeUpdate'))
                ->callbackAfterUpdate(array($this, 'eventAfterUpdate'))
                ->callbackAfterDelete(array($this, 'eventAfterDelete'))
                ->callbackAfterDeleteMultiple(array($this, 'eventAfterDeleteMultiple'));
        return $this->OrrACRUD = $acrud;
    }

    protected function getAllFields() {
        return $this->OrrACRUD->getAllFields();
    }

    protected function setMyView($output) {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        if (is_object($this->OrrACRUD)) {
            $output->view_ = $this->Sign_;
            $output->orr_ = $this->Orr_;
            $sys_child = $this->OrrACRUD->getSysChild();
            asort($sys_child[$this->Sign_['project']]);
            $menu_ = ['my_sys' => $sys_child[$this->Sign_['project']], 'projects_url' => site_url('Project'), 'mark_url' => site_url('Mark/signout'), 'mark_user' => $this->Sign_['user'], 'mark_user_icon' => "glyphicon glyphicon-user", 'mark_function' => "Sign Out", 'mark_function_icon' => "glyphicon glyphicon-log-out"];
            //$output->menu_ = ['my_sys' => $sys_child[$this->Sign_['project']]];
            $output->menu_ = $menu_;
            $output->view_['css_files'] = [base_url('assets/jquery-ui/jquery-ui.min.css'), base_url('assets/bootstrap-3/css/bootstrap.min.css')];
            $output->view_['js_files'] = [base_url('assets/jquery-3.min.js'), base_url('assets/jquery-ui/jquery-ui.min.js'), base_url('assets/bootstrap-3/js/bootstrap.min.js')];
            $this->load->view($output->view_['project'], $output);
        }
    }

    public function eventBeforeInsert($val_) {
        $sign_ = $this->Sign_;
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
        $sign_ = $this->Sign_;
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

    /**
     * Main -> Initial
     * 
     * @param type $state
     * @return type
     */
    protected function eventState($state) {
        switch ($state) {
            case 'Main':
                $this->eventMainState();
            case 'Initial':
                $this->eventInitialState();
                break;
            case 'Datagrid':
                $this->eventDatagridState();
                break;
            case 'AddForm':
                $this->eventAddFormState();
                break;
            case 'Insert':
                $this->eventInsertState();
                break;
            case 'EditForm';
                $this->eventEditFormState();
                break;
            case 'Update';
                $this->eventUpdateState();
                break;
            case 'ReadForm';
                $this->eventEditFormState();
                break;
            case 'RemoveOne';
                $this->eventRemoveOneState();
                break;
            case 'RemoveMultiple';
                $this->eventRemoveMultipleState();
                break;
            default :
            //$this->setMyJsonMessageFailure("State = $state");
        }
        return NULL;
    }

    public function eventMainState() {
        return NULL;
    }

    public function eventInitialState() {
        return NULL;
    }

    public function eventDatagridState() {
        return NULL;
    }

    public function eventAddFormState() {
        return NULL;
    }

    public function eventInsertState() {
        return NULL;
    }

    public function eventEditFormState() {
        return NULL;
    }

    public function eventUpdateState() {
        return NULL;
    }

    public function eventReadFormState() {
        return NULL;
    }

    public function eventRemoveOneState() {
        return NULL;
    }

    public function eventRemoveMultipleState() {
        return NULL;
    }

    protected function addActivityPostLog($EV_log, $EV_name) {
        $this->OrrACRUD->AddActivity($EV_name . ' : ' . $EV_log);
    }

    protected function setMyJsonMessageFailure($message) {
        $output = (object) [
                    'isJSONResponse' => TRUE,
                    'output' => json_encode(
                            (object) [
                                'message' => $message,
                                'status' => 'failure'
                            ]
                    )
        ];
        $this->setMyView($output);
        die();
    }

    protected function setMyJsonMessageSuccess($message) {
        $output = (object) [
                    'isJSONResponse' => TRUE,
                    'output' => json_encode(
                            (object) [
                                'message' => $message,
                                'status' => 'success'
                            ]
                    )
        ];
        $this->setMyView($output);
        die();
    }

}

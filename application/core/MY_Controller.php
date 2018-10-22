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

    /**
     * @var Object OrrAcrud use private
     */
    private $DbAcrud = NULL;

    /**
     * ข้อมูลผู้ใช้งาน
     * @var array จาก getSignData()
     */
    protected $Sign_ = [];
    
    /**
     * ข้อกำหนดสิทธิการใช้โปรแกรม
     * @var array จาก getAutData()
     */
    protected $Aut_ = [];
    
    /**
     * ข้อมูลการสร้าง แก้ไข ข้อมูล
     * @var array  
     */
    protected $Sec_ = [];

    /**
     * @var array ข้อมูลเกี่ยวกับอ๋อแอป [title] 
     */
    protected $Orr_ = ['title' => "Orr Projects"];

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index() {
        $this->setMyView((object) ['output' => '', 'js_files' => [], 'css_files' => []]);
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
        $this->DbAcrud = $acrud;
        $this->Sign_ = $acrud->getSignData();
        $this->Aut_ = $acrud->getAutData();
        if ($this->Sign_['status'] === 'Online') {
            $this->initACRUD();
            $this->eventState($this->DbAcrud->getState());
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

    protected function initACRUD() {
        $this->DbAcrud->unsetBootstrap()->unsetJquery()->unsetJqueryUi();
        $this->DbAcrud->setSubject('รายการใหม่', $this->Sign_['form_title']);
        $this->DbAcrud->callbackBeforeInsert(array($this, 'eventBeforeInsert'))
                ->callbackAfterInsert(array($this, 'eventAfterInsert'))
                ->callbackBeforeUpdate(array($this, 'eventBeforeUpdate'))
                ->callbackAfterUpdate(array($this, 'eventAfterUpdate'))
                ->callbackAfterDelete(array($this, 'eventAfterDelete'))
                ->callbackAfterDeleteMultiple(array($this, 'eventAfterDeleteMultiple'));
        return $this;
    }

    protected function getAllFields() {
        return $this->DbAcrud->getAllFields();
    }

    /**
     * กำหนดข้อมูลการแสดงหน้าจอ
     * @param array $output
     */
    protected function setMyView($output) {
        if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
        }

        if (is_object($this->DbAcrud)) {
            $output->view_ = $this->Sign_;
            $output->orr_ = $this->Orr_;
            $child = $this->DbAcrud->getSysChild();
            $sys_child = (array_key_exists($this->Sign_['project'], $child)) ? $child[$this->Sign_['project']] : ['' => '---'];
            $menu_ = ['my_sys' => $sys_child, 'projects_url' => site_url('Setting'), 'mark_url' => site_url('Mark/signout'), 'mark_user' => $this->Sign_['user'], 'mark_user_icon' => "glyphicon glyphicon-user", 'mark_function' => "Sign Out", 'mark_function_icon' => "glyphicon glyphicon-log-out"];
            $output->menu_ = $menu_;
            $output->view_['css_files'] = [base_url('assets/jquery-ui/jquery-ui.min.css'), base_url('assets/bootstrap-3/css/bootstrap.min.css')];
            $output->view_['js_files'] = [base_url('assets/jquery-3.min.js'), base_url('assets/jquery-ui/jquery-ui.min.js'), base_url('assets/bootstrap-3/js/bootstrap.min.js')];
            $this->load->view($output->view_['project'], $output);
        }
    }

    public function eventBeforeInsert($val_) {
        $sign_data = ['sec_owner' => $this->Sign_['user'], 'sec_user' => $this->Sign_['user'], 'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $this->Sign_['ip_address'], 'sec_script' => $this->Sign_['script']];
        $val_->data = array_merge($val_->data, $sign_data);
        return $val_;
    }

    public function eventAfterInsert($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterInsert');
        return $val_;
    }

    public function eventBeforeUpdate($val_) {
        $sign_data = ['sec_user' => $this->Sign_['user'], 'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $this->Sign_['ip_address'], 'sec_script' => $this->Sign_['script']];
        $val_->data = array_merge($val_->data, $sign_data);
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
     * เป็น ผู้ใช้งานที่สามารถแก้ไขข้อมูล
     * @return boolean คืนค่าจริง เมื่อมีสิทธิแก้ไขข้อมูล
     */
    protected function isCanEdit(){
        $this->DbAcrud->getSecData(1);
        return ($this->Aut_['aut_any'] > 1)?TRUE:($this->DbAcrud->isGod())?TRUE:FALSE;
    }

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
                ($this->isCanEdit())?$this->eventEditFormState():$this->setMyJsonMessageFailure("<b>ไม่มีสิทธิ์แก้ไขข้อมูลรายการนี้</b>");
                break;
            case 'Update';
                $this->eventUpdateState();
                break;
            case 'Upload';
                //$this->eventUploadState();
                break;
            case 'DeleteFile';
                break;
            case 'ReadForm';
                $this->eventReadFormState();
                break;
            case 'RemoveOne';
                $this->eventRemoveOneState();
                break;
            case 'RemoveMultiple';
                $this->eventRemoveMultipleState();
                break;
            case 'CloneForm';
                break;
            case 'Clone';
                break;
            default :
                $this->setMyJsonMessageFailure("State = $state");
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
        $this->DbAcrud->AddActivity($EV_name . ' : ' . $EV_log);
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

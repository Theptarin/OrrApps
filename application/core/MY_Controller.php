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
    protected $DbAcrud = NULL;

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
     * @var array ข้อมูลเกี่ยวกับอ๋อแอป [title] 
     */
    protected $Orr_ = ['title' => "Orr Projects"];
    
    /**
     *
     * @var object 
     */
    protected $Info = NULL;
    
    protected $PrimaryKeyName = "id";

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
        $this->DbAcrud->unsetBootstrap()->unsetJquery()->unsetJqueryUi()->unsetDeleteMultiple();
        $this->DbAcrud->setSubject('รายการใหม่', $this->Sign_['form_title']);
        $this->DbAcrud->callbackBeforeInsert(array($this, 'eventBeforeInsert'))
                ->callbackAfterInsert(array($this, 'eventAfterInsert'))
                ->callbackBeforeUpdate(array($this, 'eventBeforeUpdate'))
                ->callbackAfterUpdate(array($this, 'eventAfterUpdate'))
                ->callbackBeforeDelete(array($this, 'initBeforeDelete'))
                ->callbackAfterDelete(array($this, 'eventAfterDelete'))
                ->callbackAfterDeleteMultiple(array($this, 'eventAfterDeleteMultiple'))
                ->callbackEditForm(array($this, 'initEditForm'));
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

    /**
     * เป็น ผู้ใช้งานที่สามารถแก้ไขข้อมูล
     * @return boolean คืนค่าจริง เมื่อมีสิทธิแก้ไขข้อมูล
     */
    protected function isCanEdit($sec_owner) {
        if ($this->Aut_['aut_any'] > 1 || $this->DbAcrud->isGod()) {
            return TRUE;
        } else if ($this->Sign_['user'] == $sec_owner && $this->Aut_['aut_user'] > 1) {
            return TRUE;
        } elseif ($this->DbAcrud->isGroup($sec_owner) && $this->Aut_['aut_group'] > 1) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * การตรวจสอบสิทธิ์แก้ไขข้อมูล
     * @param type $val_
     * @return object
     */
    public function initEditForm($val_) {
        ($this->isCanEdit($val_['sec_owner'])) ? TRUE : $this->setMyJsonMessageFailure('<b> ไม่มีสิทธิ์แก้ไขข้อมูล รายการนี้ กรุณาแจ้ง ผู้ใช้งานที่ใช้รหัสว่า  </b>' . $val_['sec_owner']);
        return $this->eventEditForm($val_);
    }

    /**
     * เหตุการณ์ เปิดหน้าจอแก้ไขข้อมูล
     * @param type $val_
     * @return -
     */
    public function eventEditForm($val_) {
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
    /**
     * กำหนดชื่อคีย์ฟิลด์สำหรับการลบข้อมูล
     * @param string $name กำหนดชื่อคีย์สำหรับการลบข้อมูล
     */
    public function setPrimaryKeyName($name){
        $this->PrimaryKeyName = $name;
        return $this;
    }

    /**
     * เหตการณ์ก่อนการลบรายการ
     * @param object $val_
     * @return object
     */
    public function initBeforeDelete($val_) {        
        ($this->isCanDelete([$this->PrimaryKeyName => $val_->primaryKeyValue])) ? TRUE : $this->setMyJsonMessageFailure('<b> ไม่มีสิทธิ์ลบข้อมูล รายการนี้ กรุณาแจ้ง ผู้ใช้งานที่ใช้รหัสว่า  </b>' . $this->Info->sec_owner);
        return $this->eventBeforeDelete($val_);
    }

    /**
     * เหตการณ์ก่อนการลบรายการ หลังการสอบตราจสอบสิทธิ์การใช้ข้อมูล
     * @param object $val_
     * @return object
     */
    public function eventBeforeDelete($val_) {
        return $val_;
    }

    /**
     * @todo หาเหตุการณ์ที่ตรวจสอบ และวิธีหา sec_owner
     * เป็น ผู้ใช้งานที่สามารถลบข้อมูล
     * @return boolean คืนค่าจริง เมื่อมีสิทธิแก้ไขข้อมูล
     */
    protected function isCanDelete($key_) {
        $this->Info = $this->DbAcrud->getSecInfo($this->DbAcrud->getTable(), $key_);      
        $sec_owner = $this->Info->sec_owner;
        if ($this->Aut_['aut_any'] > 2 || $this->DbAcrud->isGod()) {
            return TRUE;
        } else if ($this->Sign_['user'] == $sec_owner && $this->Aut_['aut_user'] > 2) {
            return TRUE;
        } elseif ($this->DbAcrud->isGroup($sec_owner) && $this->Aut_['aut_group'] > 2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function eventAfterDelete($val_) {
        $this->addActivityPostLog(print_r($this->DbAcrud->getTable(), TRUE), 'AfterDelete');
        return $val_;
    }

    public function eventAfterDeleteMultiple($val_) {
        $this->addActivityPostLog(print_r($val_, TRUE), 'AfterDeleteMultiple');
        return $val_;
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
            case 'Print';
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
    
    public function eventPrint() {
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

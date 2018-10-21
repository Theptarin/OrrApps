<?php

/**
 * OrrApps Setting
 * Web Application framwork
 * คลาสการตั้งค่า โปรแกรม ผู้ใช้งาน การใช้ข้อมูล
 * @version 611019
 * @package OrrApps
 * @author suchart bunhachirat<suchartbu@gmail.com>
 */
class Setting extends MY_Controller {

    private $Acrud;

    /**
     * Access status
     * @var array
     */
    private $Access_ = ['0' => '0 ไม่ได้', '1' => '1 อ่านได้', '2' => '2 เขียนได้', '3' => '3 ลบได้'];
    private $Status_ = ['0' => '0 ใช้งาน', '1' => '1 ไม่ใช้งาน'];
    private $isChangePass = FALSE;

    public function __construct() {
        parent::__construct();
        $group = "orr_projects";
        $db = $this->getDbData($group);
        $this->Acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->Acrud);
    }

    /**
     * ทะเบียนโปรแกรม
     * 
     * @return void
     */
    public function mySys() {
        $crud = $this->Acrud;
        $crud->setTable('my_sys')->setClone();
        $fields = $this->getAllFields();
        $my_fields = array_merge($fields, ['user_list']);
        $crud->columns($fields)->fields($my_fields)->requiredFields(['sys_id', 'title', 'mnu_order']);
        $crud->fieldType('use_list', 'dropdown', $this->Status_)->fieldType('aut_user', 'dropdown', $this->Access_)->fieldType('mnu_order', 'int')->
                fieldType('aut_group', 'dropdown', $this->Access_)->fieldType('aut_any', 'dropdown', $this->Access_)->fieldType('aut_god', 'dropdown', $this->Status_);
        $crud->setRule('sys_id', 'regex', '/^[a-zA-Z0-9]{3,10} *_/')->setRule('mnu_order', 'integer');
        $crud->setRelationNtoN('user_list', 'my_can', 'my_user', 'sys_id', 'user_id', '{user} {fname} {lname}', 'user', ['status' => '0']);
        $crud->callbackAddForm(function ($data) {
            return array_merge($data, ['use_list' => 1, 'aut_user' => 1, 'aut_group' => 2, 'aut_any' => 1, 'aut_god' => 1]);
        });
        $output = $crud->render();
        $this->setMyView($output);
    }

    /**
     * ทะเบียนผู้ใช้งาน
     * 
     * @return void
     */
    public function myUser() {
        $crud = $this->Acrud;
        $crud->setTable('my_user')->unsetDelete()->unsetDeleteMultiple();
        $crud->columns(['user', 'prefix', 'fname', 'lname', 'status'])->requiredFields(['prefix', 'fname', 'lname'])->readOnlyEditFields(['user']);
        $crud->fieldType('status', 'dropdown', $this->Status_)->fieldType('password', 'password')->fieldType('confirm_password', 'password');
        /**
         * Bug ลบ User Group ทั้งหมดไม่ได้
         */
        $crud->setRelationNtoN('user_group', 'my_user_group', 'my_user', 'group_id', 'user_id', '{user} {fname} {lname}', 'user', ['status' => '0']);
        /**
         * Default value add form
         */
        $crud->callbackAddForm(function ($data) {
            $data['status'] = 1;
            return $data;
        });
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function myDatafield() {
        $crud = $this->Acrud;
        $crud->setTable('my_datafield');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function myActivity() {
        $crud = $this->Acrud;
        /**
         * Error on view read
         */
        $crud->setTable('activity_list')->setPrimaryKey('id', 'activity_list')->unsetOperations()->setRead()->columns($this->getAllFields());
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function mySQL() {
        redirect("http://127.0.0.1/phpmyadmin/");
    }

    public function eventBeforeInsert($val_) {
        switch ($this->Acrud->getTable()) {
            case 'my_user':
                if (!empty($val_->data['password']) && $val_->data['password'] === $val_->data['confirm_password'] && !empty($val_->data['user'])) {
                    $val_->data['val_pass'] = md5($val_->data['password']);
                } else {
                    $this->setMyJsonMessageFailure('รหัสผู้ใช้งาน หรือ รหัสผ่านไม่ถูกต้อง ');
                }
                $val_->data['password'] = "";
                $val_->data['confirm_password'] = "";
                break;

            default:
                break;
        }
        return parent::eventBeforeInsert($val_);
    }

    public function eventBeforeUpdate($val_) {
        switch ($this->Acrud->getTable()) {
            case 'my_user':
                if (!empty($val_->data['password'])) {
                    if ($val_->data['password'] === $val_->data['confirm_password']) {
                        $val_->data['val_pass'] = md5($val_->data['password']);
                        $this->isChangePass = TRUE;
                    } else {
                        $this->setMyJsonMessageFailure('ยืนยันรหัสผ่านไม่ถูกต้อง');
                    }
                }
                $val_->data['password'] = "";
                $val_->data['confirm_password'] = "";
                break;
            default:
                break;
        }
        return parent::eventBeforeUpdate($val_);
    }

    /**
     * รอแก้ไขต่อไป
     * @param type $val_
     * @return type
     */
    public function eventAfterUpdate($val_) {
        if ($this->isChangePass) {
            $sign_url = $this->Acrud->getSignUrl();
            $message = "บันทึกรหัสผ่านของ " . $val_->data['prefix'] . $val_->data['fname'] . " " . $val_->data['lname'] . "  แล้ว  ";
            $message .= "<a href=\"$sign_url\">กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่</a>";
            $this->setMyJsonMessageFailure($message);
        } else {
            switch ($this->Acrud->getTable()) {
                case 'my_sys':
                    if ($val_->data['use_list'] == 1) {
                        $this->Acrud->setUserListEmpty($val_->data['sys_id']);
                    }
                    break;
                default:
                    break;
            }
        }

        return parent::eventAfterUpdate($val_);
    }

}

<?php

/**
 * Description of Project
 *
 * @author it
 */
class Setting extends MY_Controller {

    private $acrud = NULL;

    /**
     * Project Page for this controller.
     * @todo Home Page for Orr projects.
     */
    private $use_set = ['0' => '0 ระบุ', '1' => '1 ไม่ระบุ'];
    private $aut_set = ['0' => '0 ไม่ได้', '1' => '1 อ่านได้', '2' => '2 เขียนได้', '3' => '3 ลบได้'];
    private $status_set = ['0' => '0 Active', '1' => '1 Inactive'];
    private $charge_password = FALSE;

    public function __construct() {
        parent::__construct();
        $group = "orr_projects";
        $db = $this->getDbData($group);
        $this->acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->acrud);
    }

    public function mySys() {
        $crud = $this->acrud;
        $crud->setTable('my_sys');
        $fields = $this->getAllFields();
        $my_fields = array_merge($fields, ['use_list']);

        $crud->columns($fields)->fields($my_fields);
        $crud->fieldType('any_use', 'dropdown', $this->use_set)->fieldType('aut_user', 'dropdown', $this->aut_set)->
                fieldType('aut_group', 'dropdown', $this->aut_set)->fieldType('aut_any', 'dropdown', $this->aut_set)->
                fieldType('aut_god', 'dropdown', $this->use_set);
        $crud->setRelationNtoN('use_list', 'my_can', 'my_user', 'sys_id', 'user_id', '{user} {fname} {lname}', 'user', ['status' => '0']);
        //$crud->setRelation('aut_can_from', 'my_sys', '{title} {sys_id}');
        /**
         * Default value add form
         */
        $crud->callbackAddForm(function ($data) {
            $data['any_use'] = 1;
            $data['aut_user'] = 3;
            $data['aut_group'] = 2;
            $data['aut_any'] = 1;
            $data['aut_god'] = 1;
            return $data;
        });
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function myUser() {
        $crud = $this->acrud;
        $crud->setTable('my_user')->setRead();
        $crud->columns($this->getAllFields());
        $crud->fieldType('status', 'dropdown', $this->status_set)->fieldType('password', 'password');
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
        $crud = $this->acrud;
        $crud->setTable('my_datafield');
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function myActivity() {
        $crud = $this->acrud;
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
        switch ($this->acrud->getTable()) {
            case 'my_user':
                if (!empty($val_->data['password'])) {
                    $val_->data['val_pass'] = md5($val_->data['password']);
                }
                $val_->data['password'] = "";
                break;

            default:
                break;
        }
        return parent::eventBeforeInsert($val_);
    }

    public function eventBeforeUpdate($val_) {
        switch ($this->acrud->getTable()) {
            case 'my_user':
                if (!empty($val_->data['password'])) {
                    $val_->data['val_pass'] = md5($val_->data['password']);
                    $this->charge_password = TRUE;
                }
                $val_->data['password'] = "";
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
        if ($this->charge_password) {
            $sign_url = $this->acrud->getSignUrl();
            $message = "บันทึกรหัสผ่านของ " . $val_->data['fname'] . " " . $val_->data['lname'] . " [ " . $val_->data['user'] . " ] แล้ว  ";
            $message .= "<a href=\"$sign_url\">กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่</a>";
            $this->setMyJsonMessageFailure($message);
        } else {
            switch ($this->acrud->getTable()) {
                case 'my_sys':
                    if ($val_->data['any_use'] == 1) {
                        $this->acrud->setAnyUseDefault($val_->data['sys_id']);
                    }
                    break;
                default:
                    break;
            }
        }

        return parent::eventAfterUpdate($val_);
    }

}
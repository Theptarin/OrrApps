<?php

/**
 * Description of Project
 *
 * @author it
 */
class Project extends MY_Controller {

    private $acrud = NULL;

    /**
     * Project Page for this controller.
     * @todo Home Page for Orr projects.
     */
    private $use_set = ['0' => '0 ระบุ', '1' => '1 ไม่ระบุ'];
    private $aut_set = ['0' => '0 ไม่ได้', '1' => '1 อ่านได้', '2' => '2 เขียนได้', '3' => '3 ลบได้'];
    private $status_set = ['0' => '0 Active', '1' => '1 Inactive'];

    public function __construct() {
        parent::__construct();
        $group = "orr-projects";
        $db = $this->getDbData($group);
        $this->acrud = new OrrACRUD($db, $group);
        $this->setACRUD($this->acrud);
    }

    public function my_sys() {
        $crud = $this->acrud;
        $crud->setTable('my_sys')->setSubject('MySys', 'ข้อมูลโปรแกรม');
        $fields = $this->getAllFields();
        $crud->columns($fields)->fields($fields);
        $crud->fieldType('any_use', 'dropdown', $this->use_set)->fieldType('aut_user', 'dropdown', $this->aut_set)->
                fieldType('aut_group', 'dropdown', $this->aut_set)->fieldType('aut_any', 'dropdown', $this->aut_set)->
                fieldType('aut_god', 'dropdown', $this->use_set);
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

    public function my_user() {
        $crud = $this->acrud;
        $crud->setTable('my_user')->setSubject('MyUser', 'ข้อมูลผู้ใช้งาน')->setRead();
        $crud->columns($this->getAllFields());
        $crud->fieldType('status', 'dropdown', $this->status_set)->fieldType('password','password');
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

    public function my_datafield() {
        $crud = $this->acrud;
        $crud->setTable('my_datafield')->setSubject('MyDatafield', 'คำจำกัดความข้อมูล');
        $output = $crud->render();
        $this->setMyView($output);
    }
    
    public function my_activity(){
        $crud = $this->acrud;
        /**
         * Error on view read
         */
        $crud->setTable('activity_list')->setPrimaryKey('id','activity_list')->unsetOperations()->setRead()->columns($this->getAllFields());
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function eventBeforeInsert($val_) {
        switch ($this->OrrACRUD->getTable()) {
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

}

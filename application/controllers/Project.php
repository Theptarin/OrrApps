<?php

/**
 * Description of Project
 *
 * @author it
 */
class Project extends MY_Controller {

    /**
     * Project Page for this controller.
     * @todo Home Page for Orr projects.
     */
    private $use_set = ['0' => '0 ระบุ', '1' => '1 ไม่ระบุ'];
    private $aut_set = ['0' => '0 ไม่ได้', '1' => '1 อ่านได้', '2' => '2 เขียนได้', '3' => '3 ลบได้'];
    private $status_set = ['0' => '0 Active', '1' => '1 Inactive'];

    public function __construct() {
        parent::__construct();
    }

    public function demo_set_model() {
        $crud = $this->getOrrACRUD();
        $crud->setTable('customers');
        $crud->setSubject('Customer', 'Customers');
        $crud->columns(['customerName', 'country', 'state', 'addressLine1']);
        $output = $crud->render();
        $this->setMyView($output);
    }

    public function my_sys() {
        $fields = ['sys_id', 'title', 'description', 'any_use', 'aut_user', 'aut_group', 'aut_any', 'aut_god', 'aut_can_from'];
        $crud = $this->getOrrACRUD('orr-projects');
        $crud->setTable('my_sys')->setSubject('MySys', 'ข้อมูลโปรแกรม');
        $crud->columns($fields)->fields($fields);
        $crud->fieldType($fields[3], 'dropdown', $this->use_set)->fieldType($fields[4], 'dropdown', $this->aut_set)->
                fieldType($fields[5], 'dropdown', $this->aut_set)->fieldType($fields[6], 'dropdown', $this->aut_set)->
                fieldType($fields[7], 'dropdown', $this->use_set);
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
    
    public function my_user(){
        $crud = $this->getOrrACRUD('orr-projects');
        $crud->setTable('my_user')->setSubject('MyUser', 'ข้อมูลผู้ใช้งาน')->setRead();
        $output = $crud->render();
        $this->setMyView($output);
    }
    
    public function eventBeforeInsert($val_) {
        if(!empty($val_->data['password'])){
            $val_->data['val_pass'] = md5($val_->data['password']);
        }
        $val_->data['password'] = "";
        return parent::eventBeforeInsert($val_);
    }

}

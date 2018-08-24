<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Test
 *
 * @author it
 */
class Test extends MY_Controller {
    
    private $use_set = ['0' => '0 ระบุ', '1' => '1 ไม่ระบุ'];
    private $aut_set = ['0' => '0 ไม่ได้', '1' => '1 อ่านได้', '2' => '2 เขียนได้', '3' => '3 ลบได้'];
    private $status_set = ['0' => '0 Active', '1' => '1 Inactive'];

    public function __construct() {
        parent::__construct();
    }

    public function get_ACRUD($group = 'default') {
        $db = $this->getDbData($group);
        $config = include(APPPATH . 'config/gcrud-enterprise.php');
        $acrud = new OrrACRUD($config, $db, $group);
        return parent::getACRUD($acrud);
    }

    public function my_sys() {
        $group = "orr-projects";
        $db = $this->getDbData($group);
        $crud = $this->getACRUD(new OrrACRUD($db, $group));
        $this->setMyFooter($crud->getState());
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

}

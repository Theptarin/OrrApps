<?php

/**
 * Description of Project
 *
 * @author it
 */
class Project extends MY_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function demo_set_model() {
        $crud = $this->_getGroceryCrudEnterprise();
        $crud->setTable('customers');
        $crud->setSubject('Customer', 'Customers');
        $crud->columns(['customerName', 'country', 'state', 'addressLine1']);
        $output = $crud->render();
        $this->_example_output($output);
    }

    public function my_sys() {
        $crud = $this->_getGroceryCrudEnterprise('orr-projects');
        $crud->setTable('my_sys');
        $crud->setSubject('MySys', 'ข้อมูลโปรแกรม');
        //$crud->columns(['customerName', 'country', 'state', 'addressLine1']);
        $output = $crud->render();
        $this->_example_output($output);
    }

}

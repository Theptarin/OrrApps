<?php

/**
 * Description of HIS
 *
 * @author it
 */
class HIS extends MY_Controller {

    private $acrud = NULL;

    public function __construct() {
        parent::__construct();
        $group = "ttr_hims";
        $db = $this->getDbData($group);
        $this->acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->acrud);
    }

    public function regPatient() {
        $crud = $this->acrud;
        $crud->setTable('patient')->setPrimaryKey('hn', 'patient')->unsetOperations()
                ->columns(['hn', 'fname', 'lname', 'sex', 'birthday_date', 'idcard', 'province', 'mobile'])->where(['hn > ?' => '127']);
        $output = $crud->render();
        $this->setMyView($output);
    }

}

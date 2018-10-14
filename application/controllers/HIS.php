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
        $this->Acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->Acrud);
    }

    public function regPatient() {
        $crud = $this->Acrud;
        $crud->setTable('patient')->setPrimaryKey('hn', 'patient')->unsetOperations()
                ->columns(['hn', 'fname', 'lname', 'sex', 'birthday_date', 'idcard', 'province', 'mobile'])->where(['hn > ?' => '127']);
        $output = $crud->render();
        $this->setMyView($output);
    }

}

<?php

/**
 * OrrApps Setting
 * Web Application framwork
 * ทดสอบการทำงานของระบบ
 * @package OrrApps
 * @author suchart bunhachirat<suchartbu@gmail.com>
 */
class Testing extends MY_Controller {

    private $Acrud;

    public function __construct() {
        parent::__construct();
        $group = "orr_projects";
        $db = $this->getDbData($group);
        $this->Acrud = new OrrACRUD($group, $db);
        $this->setACRUD($this->Acrud);
    }
    
    public function checkRule(){
        
    }
    
}

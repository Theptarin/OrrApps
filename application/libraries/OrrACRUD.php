<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

/**
 * Orr_ACRUD extends functions form Grocey CRUD Enterprise
 * API and Functions list
 * Access Create Read Update Delete
 * @package OrrApps
 * @author Suchart Bunhachirat <suchartbu@gmail.com>
 */
class OrrACRUD extends GroceryCrud {

    protected $authModel = NULL;
    protected $OrrModel = NULL;
    protected $secFields = ['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script', 'val_pass'];
    protected $language = 'Thai';

    public function __construct($group, $db = null) {
        $ci = &get_instance();
        $ci->load->model('OrrAuthorize');
        $this->authModel = new OrrAuthorize();
        $ci->load->model('OrrModel');
        $this->OrrModel = new OrrModel();
        if ($this->authModel->isReady()) {
            //Configulation CRUD
            $config = include(APPPATH . 'config/gcrud-enterprise.php');
            parent::__construct($config, $db);
            $this->OrrModel->setDb($group);
            $this->unsetFields($this->secFields)->unsetColumns($this->secFields)
                    ->setLanguage($this->language)->setLabelAs(['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script']);
        }
    }

    public function getSignData() {
        return $this->authModel->getSignData();
    }

    public function setLabelAs(array $fields) {
        $rows = $this->authModel->getFieldsLabel($fields);
        foreach ($rows as $field_) {
            $this->displayAs($field_['field_id'], $field_['name']);
        }
    }
    
    public function AddActivity($txt_log) {
        $this->authModel->addActivity($txt_log);
    }

    public function getAllFields() {
        return $this->OrrModel->getAllFields($this->getTable());
    }

    public function getSignUrl() {
        return site_url('Mark');
    }

    public function getSysChild() {
        return $this->authModel->getSysChild();
    }

    public function getSysParent() {
        return $this->authModel->getSysParent();
    }

    public function setUserListEmpty($sys_id) {
        $this->authModel->setUserListEmpty($sys_id);
    }
    /**
     * คืนค่าโหมดการเข้าถึงข้อมูลรายการปัจจุบันของผู้ใช้งาน
     * @param type $where
     * @return int
     */
    public function getAutMod($where){
        $sign_ = $this->getSignData();
        $row_ = $this->OrrModel->getRowAut($this->getTable(), $where);
        return 0;
    }
    
    public function isCanEdit(){
        //$row_ = $this->OrrModel->getRowAut($this->getTable(), $where);
        return TRUE;
    }
    
    public function isCanDel(){
        return FALSE;
    }

}

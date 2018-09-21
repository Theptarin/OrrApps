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
    protected $defaultAs = [];
    protected $signData = [];
    protected $secFields = ['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script', 'val_pass'];
    protected $language = 'Thai';

    public function __construct($database = null, $conn_group) {

        $ci = &get_instance();
        $ci->load->model('OrrAuthorize');
        $ci->load->model('OrrModel');
        $this->authModel = new OrrAuthorize();
        $this->OrrModel = new OrrModel();
        $this->signData = $this->getSignData();
        if ($this->signData['status'] === 'Online') {
            if ($this->authModel->getSysExist()) {
                //Configulation CRUD
                $config = include(APPPATH . 'config/gcrud-enterprise.php');
                parent::__construct($config, $database);
                $this->OrrModel->setDb($conn_group);
                $this->unsetFields($this->secFields)->unsetColumns($this->secFields)
                        ->setLanguage($this->language)->setLabelAs(['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script']);
            } else {
                /**
                 * @todo นำไปหน้าที่ตั้งค่าโปรแกรม
                 */
                die('ไม่พบโปรแกรม ' . $this->signData['script']);
            }
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

    /**
     *
     * Default value of the field.
     * field type can use : string , readonly
     * @param $field_name
     * @param $default_as
     * @return void
     */
    public function defaultAs($field_name, $default_as = null) {
        if (is_array($field_name)) {
            foreach ($field_name as $field => $default_as) {
                $this->defaultAs[$field] = $default_as;
            }
        } elseif ($default_as !== null) {
            $this->defaultAs[$field_name] = $default_as;
        }
        return $this;
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


}

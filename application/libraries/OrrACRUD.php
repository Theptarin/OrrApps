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

    protected $modelAuthorize = NULL;
    protected $modelOrr = NULL;
    protected $secFields = ['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script', 'val_pass'];
    protected $language = 'Thai';

    public function __construct($group, $db = null) {
        $ci = &get_instance();
        $ci->load->model('OrrAuthorize');
        $this->modelAuthorize = new OrrAuthorize();
        $ci->load->model('OrrModel');
        $this->modelOrr = new OrrModel();
        if ($this->modelAuthorize->isReady()) {
            //Configulation CRUD
            $config = include(APPPATH . 'config/gcrud-enterprise.php');
            parent::__construct($config, $db);
            $this->modelOrr->setDb($group);
            $this->unsetFields($this->secFields)->unsetColumns($this->secFields)
                    ->setLanguage($this->language)->setLabelAs(['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script']);
        }
    }

    public function getSignData() {
        return $this->modelAuthorize->getSignData();
    }

    public function setLabelAs(array $fields) {
        $rows = $this->modelAuthorize->getFieldsLabel($fields);
        foreach ($rows as $field_) {
            $this->displayAs($field_['field_id'], $field_['name']);
        }
    }

    public function AddActivity($txt_log) {
        $this->modelAuthorize->addActivity($txt_log);
    }

    public function getAllFields() {
        return $this->modelOrr->getAllFields($this->getTable());
    }

    public function getSignUrl() {
        return site_url('Mark');
    }

    public function getSysChild() {
        return $this->modelAuthorize->getSysChild();
    }

    public function getSysParent() {
        return $this->modelAuthorize->getSysParent();
    }

    public function getAutData() {
        return $this->modelAuthorize->getAutData();
    }

    public function setUserListEmpty($sys_id) {
        $this->modelAuthorize->setUserListEmpty($sys_id);
    }

    public function isGod() {
        return $this->modelAuthorize->isGod();
    }

    public function isGroup($sec_owner) {
        return $this->modelAuthorize->isGroup($sec_owner);
    }

}

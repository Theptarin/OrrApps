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

    protected $auth_model = NULL;
    protected $OrrModel = NULL;
    protected $default_as = [];
    protected $sign_data = [];
    protected $sec_fields = ['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script', 'val_pass'];
    protected $language = 'Thai';

    public function __construct($config, $database = null, $conn_group) {
        /**
         * Access Checking.
         */
        $ci = &get_instance();
        $ci->load->model('OrrAuthorize');
        $ci->load->model('OrrModel');
        $this->auth_model = new OrrAuthorize();
        $this->OrrModel = new OrrModel();
        $this->sign_data = $this->getSignData();
        if ($this->sign_data['status'] !== 'Online') {
            redirect(site_url('Mark'));
        } else if (!$this->auth_model->getSysExist()) {
            /**
             * @todo นำไปหน้าที่ตั้งค่าโปรแกรม
             */
            die('ไม่พบโปรแกรม ' . $this->sign_data['script']);
        }

        /**
         * Access Database.
         */
        parent::__construct($config, $database);
        $this->OrrModel->setDb($conn_group);
        $this->unsetFields($this->sec_fields)->unsetColumns($this->sec_fields)
                ->setLanguage($this->language)->setLabelAs(['sec_owner', 'sec_user', 'sec_time', 'sec_ip', 'sec_script']);
    }

    public function getSignData() {
        return $this->auth_model->getSignData();
    }

    public function setLabelAs(array $fields) {
        $rows = $this->auth_model->getFieldsLabel($fields);
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
                $this->default_as[$field] = $default_as;
            }
        } elseif ($default_as !== null) {
            $this->default_as[$field_name] = $default_as;
        }
        return $this;
    }

    public function AddActivity($txt_log) {
        $this->auth_model->addActivity($txt_log);
    }

    public function getAllFields() {
        return $this->OrrModel->getAllFields($this->getTable());
    }

}

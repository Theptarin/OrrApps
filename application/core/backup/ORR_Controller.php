<?php

/**
 * Description of ORR_Controller
 * @package Orr-projects
 * @author Suchart Bunhachirat <suchartbu@gmail.com>
 */
class ORR_Controller extends CI_Controller {

    protected $page_ = ['title' => NULL, 'sign_status' => NULL, 'topic' => NULL];
    protected $acrud;

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('Authorize_orr');
        //$this->load->library('grocery_CRUD');
        $this->load->library('orr_ACRUD');

        $sign_data_ = $this->Authorize_orr->getSignData();
        if ($sign_data_['status'] !== 'Online') {
            redirect(site_url('Mark'));
        } else if (!$this->Authorize_orr->get_sys_exist()) {
            die('ไม่พบโปรแกรม ' . $sign_data_['script']);
        }

        $this->page_['subject'] = $sign_data_['form_title'];
        $this->page_['title'] = $sign_data_['project_title'] . " " . $this->page_['subject'] ;
        //$this->page_['description'] = $sign_data_['project_description'];
    }

    /**
     * Create acrud object
     * @param Array $frm_
     * @return Object
     */
    protected function get_acrud(array $frm_) {
        $this->acrud = new OrrACRUD();
        $acrud = $this->acrud;
        $acrud->set_table($frm_['table']);
        $acrud->set_subject(isset($frm_['subject']) ? $frm_['subject'] : $this->page_['subject']);

        $acrud->callback_before_insert(array($this, 'EV_before_insert'));
        $acrud->callback_after_insert(array($this, 'EV_after_insert'));
        $acrud->callback_before_update(array($this, 'EV_before_update'));
        $acrud->callback_after_update(array($this, 'EV_after_update'));
        $acrud->callback_after_delete(array($this, 'EV_after_delete'));

        return $this->acrud;
    }

    /**
     * Create report object
     * @param array $vals
     * @return object
     */
    protected function get_tabledata(array $frm_) {
        $this->acrud = new OrrACRUD();
        $tabledata = $this->acrud;
        //$tabledata->set_theme('datatables');
        $tabledata->set_table($frm_['table']);
        //$frm_['primary_key'] = 'hn';
        $tabledata->set_primary_key($frm_['primary_key']);
        $tabledata->set_subject(isset($frm_['subject']) ? $frm_['subject'] : $this->page_['subject']);
        $tabledata->unset_add();
        $tabledata->unset_edit();
        $tabledata->unset_delete();
        return $this->acrud;
    }

    /**
     * 
     */
    protected function set_view($output, $view_name = "project:") {
        $sign_data_ = $this->Authorize_orr->getSignData();
        if (!is_array($output)) {
            $output = is_object($output) ? get_object_vars($output) : array();
        }
        $output['page_value'] = $this->page_;
        $output['css_files'] = array_merge([base_url('assets/bootstrap-4/css/bootstrap.min.css')],$output['css_files']);
        $output['js_files'] = array_merge([base_url('assets/jquery/jquery-min.js')],$output['js_files']);
        $this->load->view(isset($sign_data_['project']) ? $sign_data_['project'] : $view_name, (array) $output);
    }

    /**
     * 
     * @param Array post value
     * @return Array
     */
    public function EV_before_insert($EV_post_) {
        //$sign_ = $this->acrud->get_sign_data();
        $sign_ = $this->Authorize_orr->getSignData();
        $EV_post_['sec_owner'] = $sign_['user'];
        $EV_post_['sec_user'] = $sign_['user'];
        $EV_post_['sec_time'] = date("Y-m-d H:i:s");
        $EV_post_['sec_ip'] = $sign_['ip_address'];
        $EV_post_['sec_script'] = $sign_['script'];
        return $EV_post_;
    }

    public function EV_after_delete($EV_primary_key) {
        $EV_log = 'PKEY:' . $EV_primary_key;
        $this->add_activity_post_log($EV_log, 'After_delete');
    }

    public function EV_after_insert($EV_post, $EV_primary_key) {
        $EV_log = array_merge($EV_post, ['PKEY' => $EV_primary_key]);
        $this->add_activity_post_log($EV_log, 'After_insert');
    }

    /**
     * 
     * @param type $EV_post_
     * @return type
     */
    public function EV_before_update($EV_post_) {
        $sign_ = $this->Authorize_orr->getSignData();
        $EV_post_['sec_user'] = $sign_['user'];
        $EV_post_['sec_time'] = date("Y-m-d H:i:s");
        $EV_post_['sec_ip'] = $sign_['ip_address'];
        $EV_post_['sec_script'] = $sign_['script'];
        return $EV_post_;
    }

    /**
     * 
     * @param type $EV_post
     * @return type
     */
    public function EV_after_update($EV_post, $EV_primary_key) {
        $EV_log = array_merge($EV_post, ['PKEY' => $EV_primary_key]);
        $this->add_activity_post_log($EV_log, 'After_update');
    }

    protected function add_activity_post_log($EV_log, $EV_name) {
        if (is_array($EV_log)) {
            foreach ($EV_log as $key => $val) {
                $txt_log .= $key . ':' . $val . ";";
            }
        } else {
            $txt_log = $EV_log;
        }
        $this->Authorize_orr->addActivity($EV_name . '=;' . $txt_log);
    }

}

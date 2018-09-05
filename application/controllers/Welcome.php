<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * หน้าจอหลักของระบบ
 *
 * @author it
 */
class Welcome extends CI_Controller {

    private $page_value = ['title' => NULL, 'sign_status' => NULL, 'topic' => NULL];

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('OrrAuthorize');
    }

    /**
     * index :
     * @param String $name Description
     * @return NULL
     */
    public function index() {
        $sign_data = $this->OrrAuthorize->getSignData();
        $this->page_value = array('sign_status' => $sign_data['user'] . " - " . $sign_data['status'], 'title' => "Orr projects", 'topic' => "Welcome...");
        $this->set_view();
    }

    private function set_view($view_name = "Welcome_") {
        $html_tag_value = ['page_value' => $this->page_value, 'js_files' => array(base_url('assets/grocery-crud/js/jquery/jquery.js')), 'css_files' => array(base_url('assets/grocery-crud/css/bootstrap/bootstrap.css'))];
        $this->load->view($view_name, (array) $html_tag_value);
    }

}

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
        $sign_ = $this->OrrAuthorize->getSignData();
    if($sign_['status'] === 'Online'){
        $mnu_setting = anchor(site_url('Project'), 'ตั้งค่าระบบ', ['title' => '']);
        $mnu_sign = anchor(site_url('Mark/signout'), 'รหัสผู้ใช้ '. $sign_['user'] . ' บันทึกออก', ['title' => '']);
    }else{
        $mnu_setting = "";
        $mnu_sign = anchor(site_url('Mark'), 'บันทึกเข้า', ['title' => '']);
    }
        $this->page_value = ['sign_status' => $sign_['user'] . " - " . $sign_['status'], 'title' => "Orr projects", 'topic' => 'Welcome...','mnu_sign'=>$mnu_sign,'mnu_setting'=>$mnu_setting];
        $this->set_view();
    }

    private function set_view($view_name = "Welcome_") {
        $html_tag_value = ['page_value' => $this->page_value, 'js_files' => array(base_url('assets/grocery-crud/js/jquery/jquery.js'),base_url('assets/grocery-crud/js/libraries/jquery-ui.js')), 'css_files' => array(base_url('assets/grocery-crud/css/bootstrap/bootstrap.css'),base_url('assets/grocery-crud/css/jquery-ui/jquery-ui.css'))];
        $this->load->view($view_name, (array) $html_tag_value);
    }

}

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
        $orr_ = ['title' => "Orr Projects"];
        if ($sign_['status'] === 'Online') {
            $menu_ = ['projects_url' => site_url('Project'), 'mark_url' => site_url('Mark/signout'), 'mark_user' => $sign_['user'], 'mark_function' => "Sign Out"];
        } else {
            $menu_ = ['projects_url' => site_url('Project'), 'mark_url' => site_url('Mark'), 'mark_user' => "---", 'mark_function' => "Sign In"];
        }
        $this->setMyView((object) ['output' => '', 'js_files' => [], 'css_files' => [], 'view_' => $sign_, 'orr_' => $orr_, 'menu_' => $menu_]);
    }

    private function setMyView(object $output) {
        //$html_tag_value = ['page_value' => $this->page_value, 'js_files' => [base_url('assets/grocery-crud/js/jquery/jquery.js'),base_url('assets/grocery-crud/js/libraries/jquery-ui.js'),base_url('assets/bootstrap-3/js/bootstrap.min.js')], 'css_files' => [base_url('assets/bootstrap-3/css/bootstrap.css'),base_url('assets/grocery-crud/css/jquery-ui/jquery-ui.css')]];
        //$this->load->view($view_name, (array) $html_tag_value);
        $output->view_['css_files'] = [base_url('assets/jquery-ui/jquery-ui.min.css'), base_url('assets/bootstrap-3/css/bootstrap.min.css')];
        $output->view_['js_files'] = [base_url('assets/jquery-3.min.js'), base_url('assets/jquery-ui/jquery-ui.min.js'), base_url('assets/bootstrap-3/js/bootstrap.min.js')];
        $this->load->view('Welcome_', $output);
    }

}

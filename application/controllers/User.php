<?php

/**
 * User setting pages
 * @package OrrApps
 * @author Suchart Bunhachirat <suchartbu@gmail.com>
 */
class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('OrrAuthorize');
    }

    public function index() {
        $sign_data = $this->OrrAuthorize->getSignData();
        if ($sign_data['status'] === 'Online') {
            redirect(site_url('Welcome'));
        }
        $this->set_view();
    }

    /**
     * รับค่าจากฟอร์มบันทึกเข้าใช้ระบบ
     */
    public function signIn() {
        $this->OrrAuthorize->SignIn($this->input->post('username'), $this->input->post('password'));
        redirect('Mark');
    }

    /**
     * รับค่าจากฟอร์มบันทึกออกจากระบบ
     */
    public function signOut() {
        $this->OrrAuthorize->signOut();
        redirect('Welcome');
    }

    private function set_view($view_name = "User_") {
        $html_tag_value = ['page_value' => $this->page_value, 'js_files' => [base_url('assets/grocery-crud/js/jquery/jquery.js')], 'css_files' => [base_url('assets/grocery-crud/css/bootstrap/bootstrap.css')]];
        $this->load->view($view_name, (array) $html_tag_value);
    }

}

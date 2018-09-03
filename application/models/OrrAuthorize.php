<?php

/**
 * Orr-projects Authorize Class
 * คลาสการตรวจสอบสิทธิ์การเข้าถึงข้อมูลจากข้อมูลผู้ใช้งาน และสภาวะการเข้าใช้ระบบ
 * @package Orr-projects
 * @author Suchart Bunhachirat <suchartbu@gmail.com>
 * @version 2561
 */
class OrrAuthorize extends CI_Model {

    /**
     * List of all sign data
     * @var array 
     */
    protected $sign_data = ['id' => 0, 'user' => NULL, 'ip_address' => NULL, 'script' => NULL, 'project' => NULL, 'project_title' => NULL, 'project_description' => NULL, 'key' => NULL, 'status' => NULL];

    /**
     * Authorize db object
     */
    protected $db_auth = NULL;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->db_auth = $this->load->database('orr_projects', TRUE);

        $this->sign_data['ip_address'] = $this->getSignIpAddress();
        $this->sign_data['script'] = 'authorize_orr';
    }

    /**
     * คืนค่าสถานะการลงชื่อใช้งาน
     * @return Array
     */
    public function getSignData() {
        if ($this->session->has_userdata('sign_data')) {
            $this->setSign();
        }
        return $this->sign_data;
    }

    public function getSysExist() {
        $sql = "SELECT *  FROM `my_sys` WHERE `sys_id` = ?";
        $query = $this->db_auth->query($sql, array($this->sign_data['script']));
        if ($query->num_rows() === 1) {
            $var = TRUE;
        } else {
            $var = FALSE;
        }
        return $var;
    }

    /**
     * Checking user password when signin
     * 
     * @access public
     * @param string user name
     * @param string password
     */
    public function SignIn($user, $pass) {
        /**
         * ค้นข้อมูลจากชื่อผู้ใช้ รหัสผ่าน และสถานะ
         */
        $sql = "SELECT * FROM  `my_user`  WHERE  user = ? AND val_pass LIKE  ? AND`status` = 0 ";
        $pass = "%" . md5($pass) . "%";
        $query = $this->db_auth->query($sql, array($user, $pass));
        if ($query->num_rows() === 1) {
            /**
             * Create sing key with ip,user,sec_time
             */
            $this->sign_data['id'] = $query->row()->id;
            $this->sign_data['user'] = $query->row()->user;
            $this->sign_data['key'] = $this->getSignKey($query->row()->sec_time);
            /**
             * Create property
             */
            $this->sign_data['status'] = $this->getSignStatus(TRUE);
            $data = json_encode($this->sign_data);
            $this->session->set_userdata('sign_data', $data);
            $txt = 'User ' . $user . ' is signin.';
            $this->addActivity($txt);
        } else {
            $this->sign_data['user'] = '_ERR';
            $txt = 'User ' . $user . ' is error.';
            $this->addActivity($txt);
            $this->signOut();
        }
    }

    /**
     * ตรวจสอบสถานะการลงชื่อเข้าใช้ระบบ
     */
    private function setSign() {
        $this->sign_data = json_decode($this->session->userdata('sign_data'), TRUE);
        $sql = "SELECT * FROM  `my_user`  WHERE  id = ? AND`status` = 0 ";
        $query = $this->db_auth->query($sql, array($this->sign_data['id']));
        if ($query->num_rows() === 1) {
            if ($this->sign_data['key'] === $this->getSignKey($query->row()->sec_time)) {
                $this->sign_data['status'] = $this->getSignStatus(TRUE);
                $this->sign_data['ip_address'] = $this->getSignIpAddress();
                $this->sign_data['script'] = $this->getSignScript();
            } else {
                $this->sign_data['status'] = $this->getSignStatus(FALSE);
            }
        } else {
            die('Data record is abnormal.');
        }
    }

    /**
     * คืนค่าเป็นจริง ถ้ามีลงชื่อเข้าระบบแล้ว
     * @return boolean
     */
    public function getSign() {
        if ($this->session->has_userdata('sign_data')) {
            $this->setSign();
            $val = TRUE;
        } else {
            $val = FALSE;
        }
        return $val;
    }

    /**
     * Return Singin key
     * 
     * @access private
     * @param string Key value for create code
     * @return string
     */
    private function getSignKey($value) {
        return md5($this->sign_data['ip_address'] . $this->sign_data['user'] . $value);
    }

    /**
     * List of sign status
     * @access public
     * @return String
     */
    public function getSignStatus($is_sign) {
        if ($is_sign) {
            $this->sign_data['status'] = 'Online';
        } else {
            $this->sign_data['status'] = 'Offline';
        }
        return $this->sign_data['status'];
    }

    /**
     * IP Address for sec_ip
     * @access public
     * @return String
     */
    public function getSignIpAddress() {
        return $this->sign_data['ip_address'] = $this->input->ip_address();
    }

    /**
     * Create sing_data with format project:function.
     * @return String
     */
    public function getSignScript() {
        $ci_uri = new CI_URI();
        $this->sign_data['project'] = $ci_uri->segment(1) . '_';
        $this->setProject($this->sign_data['project']);
        $this->sign_data['script'] = $this->sign_data['project'] . $ci_uri->segment(2);
        $this->setForm($this->sign_data['script']);
        return $this->sign_data['script'];
    }

    protected function setProject($project) {
        $sql = "SELECT * FROM  `my_sys`  WHERE  sys_id = ? ";
        $query = $this->db_auth->query($sql, array($project));
        if ($query->num_rows() === 1) {
            $this->sign_data['project_title'] = $query->row()->title;
            $this->sign_data['project_description'] = $query->row()->description;
        }
    }

    protected function setForm($script) {
        $sql = "SELECT * FROM  `my_sys`  WHERE  sys_id = ? ";
        $query = $this->db_auth->query($sql, array($script));
        if ($query->num_rows() === 1) {
            $this->sign_data['form_title'] = $query->row()->title;
            $this->sign_data['form_description'] = $query->row()->description;
        }
    }

    public function addActivity($txt) {
        $data = ['description' => $txt, 'sec_user' => $this->sign_data['user'], 'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $this->sign_data['ip_address'], 'sec_script' => $this->sign_data['script']];
        $this->db_auth->insert('my_activity', $data);
    }

    public function signOut() {
        $this->sign_data['user'] = '_INF';
        $txt = 'User ' . $this->sign_data['user'] . ' is signout.';
        $this->addActivity($txt);
        $this->session->sess_destroy();
    }

    /**
     * 
     * @param Array Fields
     * @return Array
     */
    public function getFieldsLabel(array $fields) {
        $sql = "SELECT `field_id` , `name` , `description` FROM  `my_datafield`  WHERE `field_id` IN ?";
        $query = $this->db_auth->query($sql, array($fields));
        return $query->result_array();
    }

}

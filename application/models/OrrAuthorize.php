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
    protected $signData = ['id' => 0, 'user' => NULL, 'ip_address' => NULL, 'script' => NULL, 'project' => NULL, 'project_title' => NULL, 'project_description' => NULL, 'key' => NULL, 'status' => NULL];

    /**
     * Authorize db object
     */
    protected $dbAuth = NULL;
    protected $sysList = [];

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->dbAuth = $this->load->database('orr_projects', TRUE);

        $this->signData['ip_address'] = $this->getSignIpAddress();
        $this->signData['script'] = 'authorize_orr';
    }

    /**
     * คืนค่าสถานะการใช้งาน
     * @return Array ['id' = เลขผู้ใช้งาน, 'user' = รหัสผู้ใช้งาน, 'ip_address' = เลขไอพี , 'script' => รหัสโปรแกรม, 'project' = ชื่อโปรแกรม, 'project_title' = ชื่อเรียกโปรแกรม , 'project_description' = คำอธิบาย, 'key' = รหัสใช้งาน, 'status' = สถานะ]
     */
    public function getSignData() {
        if ($this->session->has_userdata('sign_data')) {
            $this->setSign();
        }
        return $this->signData;
    }

    private function setSysList() {
        $parent = [];
        $child = [];
        $query = $this->dbAuth->query("SELECT * FROM `my_sys`");
        foreach ($query->result() as $row) {
            $id = explode("_", $row->sys_id);
            if ($id[1] === "") {
                $parent[$id[0]] = $row->title;
            } else {
                $parent_id = $id[0] ."_";
                $child[$parent_id]=array_key_exists($parent_id, $child)?array_merge($child[$parent_id],[$id[1] => $row->title]):[$id[1] => $row->title];
            }
        }
        $this->sysList['parent'] = $parent;
        $this->sysList['child'] = $child;
    }

    public function getSysParent() {
        $this->setSysList();
        return $this->sysList['parent'];
    }

    public function getSysChild() {
        $this->setSysList();
        return $this->sysList['child'];
    }

    public function getSysExist() {
        $sql = "SELECT *  FROM `my_sys` WHERE `sys_id` = ?";
        $query = $this->dbAuth->query($sql, array($this->signData['script']));
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
        $query = $this->dbAuth->query($sql, array($user, $pass));
        if ($query->num_rows() === 1) {
            /**
             * Create sing key with ip,user,sec_time
             */
            $this->signData['id'] = $query->row()->id;
            $this->signData['user'] = $query->row()->user;
            $this->signData['key'] = $this->getSignKey($query->row()->sec_time);
            /**
             * Create property
             */
            $this->signData['status'] = $this->getSignStatus(TRUE);
            $data = json_encode($this->signData);
            $this->session->set_userdata('sign_data', $data);
            $txt = 'User ' . $user . ' is signin.';
            $this->addActivity($txt);
        } else {
            $this->signData['user'] = '_ERR';
            $txt = 'User ' . $user . ' is error.';
            $this->addActivity($txt);
            $this->signOut();
        }
    }

    /**
     * ตรวจสอบสถานะการลงชื่อเข้าใช้ระบบ
     */
    private function setSign() {
        $this->signData = json_decode($this->session->userdata('sign_data'), TRUE);
        $sql = "SELECT * FROM  `my_user`  WHERE  id = ? AND`status` = 0 ";
        $query = $this->dbAuth->query($sql, array($this->signData['id']));
        if ($query->num_rows() === 1) {
            if ($this->signData['key'] === $this->getSignKey($query->row()->sec_time)) {
                $this->signData['status'] = $this->getSignStatus(TRUE);
                $this->signData['ip_address'] = $this->getSignIpAddress();
                $this->signData['script'] = $this->getSignScript();
            } else {
                $this->signData['status'] = $this->getSignStatus(FALSE);
            }
        } else {
            $this->signOut();
            die('Sign Data record is abnormal.');
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
        return md5($this->signData['ip_address'] . $this->signData['user'] . $value);
    }

    /**
     * List of sign status
     * @access public
     * @return String
     */
    public function getSignStatus($is_sign) {
        if ($is_sign) {
            $this->signData['status'] = 'Online';
        } else {
            $this->signData['status'] = 'Offline';
        }
        return $this->signData['status'];
    }

    /**
     * IP Address for sec_ip
     * @access public
     * @return String
     */
    public function getSignIpAddress() {
        return $this->signData['ip_address'] = $this->input->ip_address();
    }

    /**
     * Create sing_data with format project:function.
     * @return String
     */
    public function getSignScript() {
        $ci_uri = new CI_URI();
        $this->signData['project'] = $ci_uri->segment(1) . '_';
        $this->setProject($this->signData['project']);
        $this->signData['script'] = $this->signData['project'] . $ci_uri->segment(2);
        $this->setForm($this->signData['script']);
        return $this->signData['script'];
    }

    protected function setProject($project) {
        $sql = "SELECT * FROM  `my_sys`  WHERE  sys_id = ? ";
        $query = $this->dbAuth->query($sql, array($project));
        if ($query->num_rows() === 1) {
            $this->signData['project_title'] = $query->row()->title;
            $this->signData['project_description'] = $query->row()->description;
        }
    }

    /**
     * กำหนดค่า from title และ from description
     * @param string $script
     */
    protected function setForm($script) {
        $sql = "SELECT * FROM  `my_sys`  WHERE  sys_id = ? ";
        $query = $this->dbAuth->query($sql, array($script));
        if ($query->num_rows() === 1) {
            $this->signData['form_title'] = $query->row()->title;
            $this->signData['form_description'] = $query->row()->description;
        }
    }

    public function addActivity($txt) {
        $data = ['description' => $txt, 'sec_user' => $this->signData['user'], 'sec_time' => date("Y-m-d H:i:s"), 'sec_ip' => $this->signData['ip_address'], 'sec_script' => $this->signData['script']];
        $this->dbAuth->insert('my_activity', $data);
    }

    public function signOut() {
        $this->signData['user'] = '_INF';
        $txt = 'User ' . $this->signData['user'] . ' is signout.';
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
        $query = $this->dbAuth->query($sql, array($fields));
        return $query->result_array();
    }

}

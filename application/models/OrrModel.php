<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrrModel
 *
 * @author it
 */
class OrrModel extends CI_Model {

    protected $db = NULL;

    public function __construct() {
        parent::__construct();
    }

    public function setDb($conn_group) {
        $this->db = $this->load->database($conn_group, TRUE);
    }

    public function getAllFields($table) {
        return $this->db->list_fields($table);
    }

    /**
     * คืนค่าคุณสมบัติของฟิลด์ทั้งหมดในตารางข้อมูล
     * @param string $table
     * @return object fields meta-data
     */
    public function getFieldInfo($table) {
        return $this->db->field_data($table);
    }

    /**
     * คืนค่าการสร้าง แก้ไข รายการข้อมูล
     * @param string $table
     * @param array $sec_keys ชื่อฟิลด์และค่าที่ใช้ค้นหารายการ ค่าเริ่มเป็น ['id'=>NULL]
     * @return objects ข้อมูลรหัสเจ้าของข้อมูล รหัสผู้แก้ไข เวลาที่แก้ไข IP Address ชื่อโปรแกรมที่บันทึกรายการ
     */
    public function getSecInfo($table, $sec_keys) {
        $this->db->select('sec_owner,sec_user,sec_time,sec_ip,sec_script');
        $query = $this->db->get_where($table, $sec_keys);
        return $query->row();
    }

}

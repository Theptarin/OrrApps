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

    public function getSecOwner($table, $key_name = 'id') {
        $this->db->select($key_name . ', sec_owner');
        $query = $this->db->get($table);
        //return "root";
        return ($query->num_rows() === 1)?$query->row()->sec_owner:"";
    }

}

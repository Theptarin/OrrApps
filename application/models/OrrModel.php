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
        $this->db = $this->load->database('orr-projects', TRUE);
    }

    public function setDb($conn_group) {
        
    }

    public function getAllFields() {
        // Error
        $fields = $this->db->list_fields('my_user');
        return $fields;
    }

}

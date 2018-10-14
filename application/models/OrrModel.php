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
     * 
     * @param string $table
     * @param array $where
     */
    public function getRowAut($table,$where){
        $query = $this->db->get_where($table, $where);
        $row = $query->row_array();
        return (isset($row))?$row:NULL;
    }
    
    public function getPrimaryKayName(){
        
    }

}

<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');

use GroceryCrud\Core\GroceryCrud;

/**
 * Description of OrrLibs
 *
 * @author it
 */
class OrrLibs extends GroceryCrud {
    public function __construct($config, $database = null) {
        parent::__construct($config, $database);
    }
    
    public function setTest(){
        
    }
}

class OrrCrud extends OrrLibs{
    public function __construct($config, $database = null) {
        parent::__construct($config, $database);
    }
}

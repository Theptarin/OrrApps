<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include(APPPATH . 'libraries/GroceryCrudEnterprise/autoload.php');
use GroceryCrud\Core\GroceryCrud;

class Examples extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->helper('url');

		// You can simply remove these lines once you have base_url installed correctly
		$config = [];
		include(APPPATH . 'config/config.php');
		if ($config['base_url'] === '') {
			show_error('You forgot to add the $config[\'base_url\'] at application/config/config.php . For more check the video tutorial here: <a href="https://youtu.be/X0gnDD0qTS8?t=24s" target="_blank">Common mistakes when we install Grocery CRUD</a>');
		}

		// In case you will need grocery CRUD community edition, just
		// uncomment the below line :)
		// $this->load->library('grocery_CRUD');   
	}

	public function _example_output($output = null)
	{
		if (isset($output->isJSONResponse) && $output->isJSONResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo $output->output;
            exit;
		}

		$this->load->view('example.php',(array)$output);
	}

	public function index()
	{
		$this->_example_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
	}

	public function customers_management()
	{
		$crud = $this->_getGroceryCrudEnterprise();

		$crud->setTable('customers');
		$crud->setSubject('Customer', 'Customers');
		$crud->columns(['customerName','contactLastName','phone','city','country','salesRepEmployeeNumber','creditLimit']);
		$crud->displayAs('salesRepEmployeeNumber','from Employeer')
			 ->displayAs('customerName','Name')
			 ->displayAs('contactLastName','Last Name');
		$crud->setRelation('salesRepEmployeeNumber','employees','lastName');

		$output = $crud->render();

		$this->_example_output($output);
	}

	private function _getDbData() {
        $db = [];
        include(APPPATH . 'config/database.php');
        return [
            'adapter' => [
                'driver' => 'Pdo_Mysql',
                'host'     => $db['default']['hostname'],
                'database' => $db['default']['database'],
                'username' => $db['default']['username'],
                'password' => $db['default']['password'],
                'charset' => 'utf8'
            ]
        ];
    }
	
	private function _getGroceryCrudEnterprise($bootstrap = true, $jquery = true) {
	        $db = $this->_getDbData();
	        $config = include(APPPATH . 'config/gcrud-enteprise.php');
	        $groceryCrud = new GroceryCrud($config, $db);
	        return $groceryCrud;
	}
}

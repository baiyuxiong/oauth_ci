<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('oauth_lib');
	}

	function index()
	{
		//$this->load->view('lib\oauth\loginicon.php');
		$this->oauth_lib->loadLoginIcons();
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
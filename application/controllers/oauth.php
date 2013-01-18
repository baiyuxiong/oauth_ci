<?php
class Oauth extends CI_Controller
{

    function __construct ()
    {
        parent::__construct();
    }

    function index ()
    {
        redirect();
    }
    
    function login()
    {
    	$this->load->config('oauth');
    	$site = $this->uri->segment('3');
    	
    	ll('oauth_lib');
    	$this->oauth_lib->login($site);
    }
}




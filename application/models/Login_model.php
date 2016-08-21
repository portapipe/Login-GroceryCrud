<?php
	
class Login_model  extends CI_Model  {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('session');
	}
	
	/* CHECK LOGIN, return BOOL */
	public function isLogged(){
		if($this->session->userdata('loginStatus')){
			return true;
		}
		return false;
	}
	
	/* RETURN PERMISSION FIELD (the one you save into the session, if no permission was set so the username is returned */
	public function permission(){
		return $this->session->userdata('loginStatus');
	}
	public function permissions(){
		return $this->permission();
	}
	public function getPermission(){
		return $this->permission();
	}
	public function getPermissions(){
		return $this->permission();
	}
	

	/* LOGOUT */
	public function logout(){
		$this->session->sess_destroy();
		redirect("/login");
	}


}

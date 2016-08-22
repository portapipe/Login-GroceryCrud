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
		if(null !== $this->session->userdata('loginStatus')){
			return 1;
		}
		return 0;
	}
	
	/* RETURN PERMISSION FIELD (the one you save into the session, if no permission was set so the username is returned */
	public function permission(){
		$data = json_decode($this->session->userdata('loginStatus'));
		return $data->permissions;
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
	
	public function id(){
		$data = json_decode($this->session->userdata('loginStatus'));
		return $data->id;
	}
	public function name(){
		$data = json_decode($this->session->userdata('loginStatus'));
		return $data->username;
	}
	public function username(){
		return $this->name();
	}
	
	
	/* LOGOUT */
	public function logout($redirect=true){
		$this->session->sess_destroy();
		if($redirect) redirect("/login");
	}


}

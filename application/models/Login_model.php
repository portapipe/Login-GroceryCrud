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
	
	public function getField($field){
		return $this->db->query("SELECT $field FROM crud_users WHERE id = ".$this->id())->row()->$field;
	}
	
	
	/* LOGOUT */
	public function logout($redirect=true){
		$this->session->sess_destroy();
		if($redirect) redirect("/login");
	}


	/* FROM HERE THE PERMISSION MANAGEMENT SYSTEM */
	public function extractPermission($what,$permission=false,$table=false){
		/*
		ID  RL  RS  A  E  D
		x   x   x   x  x  x
		*/
		
		if(!$permission){
			$query = $this->db->query("SELECT permissions FROM crud_permissions WHERE id = ".$this->permission());
			$permission = json_decode($query->row()->permissions,true);
			if(!$table){ echo "You need to pass a table to 'this->login_model->extractPermission()' as third parameter to use the current logged user permissions!";die;}
			if(isset($permission[$table])){
				$permission = $permission[$table];
			}else{
				$permission = 100000;
			}
		}
		
		$return = 0;
		if($what == 1||$what == "ID"||$what=="idonly"){
			if($permission[0]) $return = 0;
			if(!$permission[0]) $return = 1;
		}
		if($what == 2||$what == "RL"||$what=="readlist") $return = $permission[1];
		if($what == 3||$what == "RS"||$what=="readsingle") $return = $permission[2];
		if($what == 4||$what == "A"||$what=="add") $return = $permission[3];
		if($what == 5||$what == "E"||$what=="edit") $return = $permission[4];
		if($what == 6||$what == "D"||$what=="delete") $return = $permission[5];
		return $return;
	}
	
	
	public function IDOnly($table,$permission=000000){

		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("id",$permission);		
	}
	
	public function canSeeList($table,$permission=000000){
		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("RL",$permission);		
	}
	public function canSeeSingle($table,$permission=000000){
		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("RS",$permission);		
	}
	public function canAdd($table,$permission=000000){
		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("A",$permission);		
	}
	public function canEdit($table,$permission=000000){
		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("E",$permission);		
	}
	public function canDelete($table,$permission=100000){
		if(is_array($permission) && isset($permission[$table])){
			$permission = $permission[$table];
		}
		if(!is_numeric($permission)) $permission = 100000;
		return $this->extractPermission("D",$permission);		
	}
	
	public function check($crud,$author=false){
		//print_r($crud->Grocery_CRUD);
		//$state = (array) $crud;//$crud->basic_db_table;
		//$state = serialize($state['basic_model']);//["*state_info"];
		//$state = $crud;
		$state = unserialize(
			preg_replace(
				'/^O:\d+:"[^"]++"/', 
				'O:'.strlen("portapipe").':"portapipe"',
				serialize($crud)
			)
		);
		$state = json_encode((array)$state);
		$state2 = strpos($state,'basic_db_table":"');
		$state = str_replace('basic_db_table":"','',substr($state, $state2));
		$state2 = strpos($state,'"');
		$table = substr($state, 0, $state2);
		if(!$this->extractPermission("RL",false,$table)) $crud->unset_list();
		if(!$this->extractPermission("RS",false,$table)) $crud->unset_read();
		if(!$this->extractPermission("D",false,$table)) $crud->unset_delete();
		if(!$this->extractPermission("A",false,$table)) $crud->unset_add();
		if(!$this->extractPermission("E",false,$table)) $crud->unset_edit();
		if(!$this->extractPermission("D",false,$table)) $crud->unset_delete();
		
		if($author) $crud->where($author,$this->id());
		
		return $crud;
	}

}

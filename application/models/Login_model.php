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
	}/*aliases*/public function permissions(){return $this->permission();} public function getPermission(){return $this->permission();} public function getPermissions(){return $this->permission();}
	
	// Return the id of the current logged user
	public function id(){
		$data = json_decode($this->session->userdata('loginStatus'));
		return $data->id;
	}
	
	// Return the username of the current logged user
	public function name(){
		$data = json_decode($this->session->userdata('loginStatus'));
		return $data->username;
	}/*aliases*/public function username(){return $this->name();}
	
	//Return the specified field into the crud_users database of the current logged user
	public function getField($field){
		return $this->db->query("SELECT $field FROM crud_users WHERE id = ".$this->id())->row()->$field;
	}
	
	
	/* LOGOUT the current user */
	public function logout($redirect=true){
		$this->session->sess_destroy();
		if($redirect) redirect("/login");
	}


	/* FROM HERE THE PERMISSION MANAGEMENT SYSTEM */
	/*             JUST GROCERYCRUD               */
	// check the permission of a user for a specific action in a specific table
	// return a boolean
	public function extractPermission($what,$permission=false,$table=false){
		/*
		ID  RL  RS  A  E  D
		x   x   x   x  x  x
		*/
		if(is_bool($permission)){
			$query = $this->db->query("SELECT permissions FROM crud_permissions WHERE id = ".$this->permission());
			$permission = json_decode($query->row()->permissions,true);
			if(!$table){ echo "You need to pass a table to 'this->login_model->extractPermission()' as third parameter to use the current logged user permissions!";die;}
			if(isset($permission[$table])){
				$permission = $permission[$table];
			}else{
				$permission = 100000;
			}
		}else{
			if(is_array($permission) && isset($permission[$table])){
				$permission = $permission[$table];
			}else{
				if(!is_numeric($permission)) $permission = 100000;
			}
		}
		$return = 0;
		if($what == "ID"||$what=="idonly"){
			if($permission[0].""==""){
				$return = 0;
			}else{
				$return = ($permission[0]?0:1);
			}
		}
		if($what == 2||$what == "RL"||$what=="readlist") $return = $permission[1];
		if($what == 3||$what == "RS"||$what=="readsingle") $return = $permission[2];
		if($what == 4||$what == "A"||$what=="add") $return = $permission[3];
		if($what == 5||$what == "E"||$what=="edit") $return = $permission[4];
		if($what == 6||$what == "D"||$what=="delete") $return = $permission[5];
		return $return;
	}
	
	//Check if perm allow to see IDOnly or All
	public function IDOnly($table,$permission=true){
		return $this->extractPermission("ID",$permission,$table);		
	}
	//Check if perm allow to see grid list or not
	public function canSeeList($table,$permission=true){
		return $this->extractPermission("RL",$permission,$table);		
	}
	//Check if perm allow to see the single view of the records
	public function canSeeSingle($table,$permission=true){
		return $this->extractPermission("RS",$permission,$table);		
	}
	//Check if perm allow to add a record
	public function canAdd($table,$permission=true){
		return $this->extractPermission("A",$permission,$table);		
	}
	//Check if perm allow to edit a record
	public function canEdit($table,$permission=true){
		return $this->extractPermission("E",$permission,$table);		
	}
	//Check if perm allow to delete a record
	public function canDelete($table,$permission=true){
		return $this->extractPermission("D",$permission,$table);		
	}
	
	//The function that MUST be used to filter the CRUD table based on the permissions
	public function check($crud,$author=false){
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

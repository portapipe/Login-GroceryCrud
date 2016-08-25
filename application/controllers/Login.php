<?php
$loginConfig = array(
	"Page After Login" => "/",
	"Error Message" => "Your Username or Password are incorrect!",
	"Use MD5 Encryption" => false
);

/* INSTRUCTIONS? GO TO https://github.com/portapipe/Login-GroceryCrud */
/* Login-GroceryCrud by portapipe */
class Login extends CI_Controller {


	public function __construct(){
		parent::__construct();	
		$this->load->database();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('login_model');
	}

	/* LOGIN PAGE */
	function index() {
		global $loginConfig;
		if($this->login_model->isLogged()) redirect($loginConfig['Page After Login']);
		if ($this->db->table_exists('crud_users')){
			$this->load->view('login.php');
		}else{
			echo "SYSTEM REQUIREMENT: SQL TABLE crud_users DOESN'T EXISTS BUT... Hey, we can do it for you, if the database connection is configured correctly ;)<p><a href=\"".base_url()."login/createDBTable\"><button>Create the required table in your MySQL database</button></a></p>Oh, a user 'admin' (password 'admin') will be create too, so you can log in directly! Just click on the button!";
			echo "<p><br/><i>...this is a one-time-only step!</i></p>";
		}

	}
	
	/* LOGIN PROCESS */
	function makeLogin() {
		global $loginConfig;
		
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		if($loginConfig['Use MD5 Encryption']) $password = md5($password);
		
		$query = $this->db->query("SELECT * FROM crud_users WHERE username='$username' AND password='$password'");
		if ($query->num_rows() == 1) {
			$name = $query->row()->username;
			$permissions = (isset($query->row()->permissions)?$query->row()->permissions:"");
			$data = json_encode(array("id"=>$query->row()->id,"permissions"=>$query->row()->permissions,"username"=>$query->row()->username));
			$this->session->set_userdata('loginStatus',$data);
			redirect($loginConfig['Page After Login']);
		}else{
			/* ERROR PART */
			$data['error']= $loginConfig['Error Message'];
			$this->load->view('login.php', $data);
		}
	}

	/* LOGOUT */
	function logout() {
		$this->login_model->logout();
		redirect("/login");
	}
	
	function createDBTable(){
		if ($this->db->table_exists('crud_users')){
		    redirect(base_url()."login");
		}else{
			$this->db->query("CREATE TABLE crud_users (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				username VARCHAR(70) NOT NULL,
				password VARCHAR(70) NOT NULL,
				permissions VARCHAR(255)
			)");
			$this->db->query("INSERT INTO crud_users (username,password,permissions) VALUES ('admin','admin','1')");
			echo "The table 'crud_users' was successfully created!<br/>An admin user was created too. Login with:<br/>- username 'admin'<br/>- password 'admin'<br/>AND DON'T FORGET TO DELETE THIS USER! IS YOUR RESPONSIBILITY!<br/>Really, delete it as soon as you can (or at least change the password)!";
			echo '<p><a href="'.base_url().'login"><button>Go to the Login Page</button></a></p>'; 
		}
	}
	
	function createDBTableForPermissions(){
		if (!$this->db->table_exists('crud_permissions')){
			$this->db->query("CREATE TABLE crud_permissions (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				name VARCHAR(70) NOT NULL,
				permissions TEXT
			)");
			/*
			
			
			ID  RL  RS  A  E  D
			x   x   x   x  x  x
			
			ID. about reading 1 all records, 0 just the one added from the user	
			RL. see 1 the list or not 0
			RS. see 1 the single page of the record or not 0 
			A.  add 1 a new record or not 0
			E.  edit 1 the record or not 0
			D.  delete 1 the record or not 0
			
			Example:
			A Blogger can add, edit and delete (full view) his records
			011111
			A Reviewer can edit and delete (full view) any record
			111011
			A guest can just see the list of all records
			110000
			
			
			*/
			$dati = array("crud_permissions"=>"111111","crud_users"=>"111111");
			$this->db->query("INSERT INTO crud_permissions (name,permissions) VALUES ('admin','".json_encode($dati)."')");
			
		}
	}
	//page to manage all the permissions
	// IT WORKS JUST WITH GROCERYCRUD !!!!!
	public function manage_permissions(){
		if(!$this->login_model->isLogged()){ redirect(base_url()."login"); return;}
		$this->createDBTableForPermissions();
		
		$this->load->library('grocery_CRUD');
		
		$crud = new grocery_CRUD();
		$crud->set_theme("bootstrap");
		$crud->set_table('crud_permissions');
		$crud->set_subject('User Permission');
		$crud->required_fields('name');
        $crud->columns('name');

		$crud->unset_read();

		$crud->callback_field('permissions',array($this,'create_permissions_grid'));
		$crud->callback_before_update(array($this,'elaborate_the_grid_then_update'));
		$crud->callback_before_insert(array($this,'elaborate_the_grid_then_update'));
		$crud = $this->login_model->check($crud);
		$output = $crud->render();
		
		?>
		<html>
			<head>
			<title>Permissions Management</title>
			<?php 
			foreach($output->css_files as $file): ?>
				<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
			<?php endforeach; ?>
			<?php foreach($output->js_files as $file): ?>
				<script src="<?php echo $file; ?>"></script>
			<?php endforeach; ?>
			<style>
			.checkbox-grid li {
			    display: block;
			    float: left;
			    width: 14.285714286%;
			    border: 1px solid rgba(0, 0, 0, 0.49);
			    padding: 5px;
			    text-align: center;
			    height: 30px;
			}
			</style>
			</head>
			<body>
				<? echo $output->output; ?>
			</body>
		</html>
		<?		
	}
	
	function create_permissions_grid($value='', $primary_key = null){
		$perm = json_decode($value,true);
		$return = '<ul class="checkbox-grid">';
		$arr = array("ID Only","Read List","Read Single","Add New","Edit","Delete");
		$tables = $this->db->list_tables();

		//ID
		$return .= '
		<li>Tables</li>
		';
		foreach($arr as $a){
			$return .= '
			<li>'.$a.'</li>
			';
		}
		foreach($tables as $a){
		$return .= '
	    <li>'.$a.'</li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[1]" value="0" '.($this->login_model->IDOnly($a,$perm)?'checked':'').'/></li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[2]" value="1" '.($this->login_model->canSeeList($a,$perm)?'checked':'').'/></li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[3]" value="1" '.($this->login_model->canSeeSingle($a,$perm)?'checked':'').'/></li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[4]" value="1" '.($this->login_model->canAdd($a,$perm)?'checked':'').'/></li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[5]" value="1" '.($this->login_model->canEdit($a,$perm)?'checked':'').'/></li>
	    ';
		$return .= '
	    <li><input type="checkbox" name="'.$a.'[6]" value="1" '.($this->login_model->canDelete($a,$perm)?'checked':'').'/></li>
	    ';
	    }
		$return .= '</ul>';
		return $return;
	}
	function elaborate_the_grid_then_update($post_array, $primary_key=null) {
		foreach($post_array as $key=>$val){
			if($key=="name") continue;
			
			if(!isset($post_array[$key][1])) $post_array[$key][1] = 0;
			if(!isset($post_array[$key][2])) $post_array[$key][2] = 0;
			if(!isset($post_array[$key][3])) $post_array[$key][3] = 0;
			if(!isset($post_array[$key][4])) $post_array[$key][4] = 0;
			if(!isset($post_array[$key][5])) $post_array[$key][5] = 0;
			if(!isset($post_array[$key][6])) $post_array[$key][6] = 0;
			$p = $post_array[$key];
			$permissions[$key] = $p[1].$p[2].$p[3].$p[4].$p[5].$p[6];
				
		}
		$post_array = array("name"=>$post_array['name'],"permissions"=>json_encode($permissions));
		 
		return $post_array;
	}
	
}


?>

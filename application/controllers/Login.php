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
			$this->session->set_userdata('loginStatus',($permissions!=""?$permissions:$name));
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
			$this->db->query("INSERT INTO crud_users (username,password,permissions) VALUES ('admin','admin','admin')");
			echo "The table 'crud_users' was successfully created!<br/>An admin user was created too. Login with:<br/>- username 'admin'<br/>- password 'admin'<br/>AND DON'T FORGET TO DELETE THIS USER! IS YOUR RESPONSIBILITY!<br/>Really, delete it as soon as you can (or at least change the password)!";
			echo '<p><a href="'.base_url().'login"><button>Go to the Login Page</button></a></p>'; 
		}
	}
}
?>

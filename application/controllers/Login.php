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
		$this->load->view('login.php');
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
}
?>

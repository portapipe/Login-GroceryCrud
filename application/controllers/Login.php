<?php
$loginConfig = array(
	"Page After Login" => "/",
	"Error Message" => ""
);

class Login extends Controller {

	function login() {
		parent::Controller();
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->library('session');
	}

	/* LOGIN PAGE */
	function index() {
		$this->load->view('loginPage');
	}
	
	/* LOGIN PROCESS */
	function makeLogin() {
		global $loginConfig;
		
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$passwordMD5 = md5($password);
		$query = $this->db->query("SELECT * FROM crud_users WHERE username='$username' AND password='$passwordMD5'");
		if ($query->num_rows() == 1) {
			$name = $query->row()->username;
			$permissions = $query->row()->permissions."";
			$this->session->set_userdata('username',($permissions!=""?$permissions:$name));
			redirect($loginConfig['Page After Login']);
		}else{
			/* ERROR PART */
			$data['error']= $loginConfig['Error Message'];
			$this->load->view('loginPage', $data);
		}
	}

	/* LOGOUT */
	function logout() {
		$this->session->sess_destroy();
		redirect("/login");
	}
}
?>

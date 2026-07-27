<?php
class Login extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('M_login', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin') != null) {
			redirect('dashboard');
		}
		$data['title'] = 'Login';

		$this->load->view('login', $data);
	}



	public function masuk()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$user_data = $this->model->login($username, $password);



		if ($user_data['id_user'] != null) {
			$this->session->set_userdata('admin', $user_data);
			redirect('dashboard');
		} else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">Username atau password tidak terdaftar!</div>');
			redirect('/');
		}
	}


	public function keluar()
	{
		$this->session->unset_userdata('logged_in');
		$this->session->unset_userdata('id');
		$this->session->unset_userdata('nisn');
		$this->session->unset_userdata('password');
		$this->session->sess_destroy();

		redirect('/');
	}
}
?>
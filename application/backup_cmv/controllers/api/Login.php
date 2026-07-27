<?php
class Login extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('api/M_login', 'model');
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
		header('Content-Type: application/json');

		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$user_data = $this->model->login($username, $password);

		if ($user_data && isset($user_data['id_user'])) {
			echo json_encode([
				'status' => true,
				'message' => 'Login berhasil',
				'data' => $user_data
			]);
		} else {
			echo json_encode([
				'status' => false,
				'message' => 'Username atau password tidak terdaftar'
			]);
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

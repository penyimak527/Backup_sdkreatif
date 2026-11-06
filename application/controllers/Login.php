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
		$session = $this->session->userdata('admin');
		if (!is_array($session) || empty($session['id_user'])) {
			$session = $this->session->userdata('wali');
		}
		if (is_array($session) && !empty($session['id_user'])) {
			redirect($this->halaman_awal($session));
		}
		$data['title'] = 'Login';

		$this->load->view('login', $data);
	}



	public function masuk()
	{
		$username = $this->input->post('username');
		$password = $this->input->post('password');
		$user_data = $this->model->login($username, $password);



		if (is_array($user_data) && !empty($user_data['id_user'])) {
			$this->session->sess_regenerate(true);
			if ($this->adalah_wali($user_data)) {
				$this->session->unset_userdata('admin');
				$this->session->set_userdata('wali', $user_data);
			} else {
				$this->session->unset_userdata('wali');
				$this->session->set_userdata('admin', $user_data);
			}
			redirect($this->halaman_awal($user_data));
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

	private function halaman_awal($user_data)
	{
		return $this->adalah_wali($user_data) ? 'wali/dompet' : 'dashboard';
	}

	private function adalah_wali($user_data)
	{
		return (int) ($user_data['id_level'] ?? 0) === 8
			|| strtoupper(trim((string) ($user_data['level'] ?? ''))) === 'WALI MURID';
	}
}
?>

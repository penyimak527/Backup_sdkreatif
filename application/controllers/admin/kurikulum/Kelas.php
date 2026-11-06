<?php
class Kelas extends CI_Controller
{
	const MENU_ID = 9;

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_kelas', 'model');
		$this->load->library('web_guard');
		$this->web_guard->enforce_login($this->router->fetch_method() !== 'index');
	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['title'] = 'Kelas';
		$data['csrf_token'] = $this->web_guard->csrf_token();
		$data['soft_delete_available'] = $this->model->supports_soft_delete();
		$this->load->view('template/header', $data);
		$this->load->view('admin/kurikulum/kelas', $data);
		$this->load->view('template/footer');
	}

	public function siswa_result()
	{
		$data = $this->model->siswa_result();

		echo json_encode($data);
	}
	public function kelas_result()
	{
		$data = $this->model->kelas_result();

		echo json_encode($data);
	}
	public function kelas_setting_result()
	{
		$data = $this->model->kelas_setting_result();

		echo json_encode($data);
	}
	public function tambah()
	{
		$this->web_guard->enforce_csrf();
		$data = $this->model->tambah();

		echo json_encode($data);
	}
	public function tambah_siswa()
	{
		$data = $this->model->tambah_siswa();

		echo json_encode($data);
	}
	public function edit()
	{
		$this->web_guard->enforce_csrf();
		$data = $this->model->edit();

		echo json_encode($data);
	}

	public function hapus()
	{
		$this->web_guard->enforce_csrf();
		$data = $this->model->hapus();

		echo json_encode($data);
	}

	public function trash()
	{
		echo json_encode($this->model->trash_result());
	}

	public function restore()
	{
		$this->web_guard->enforce_csrf();
		echo json_encode($this->model->restore());
	}

}
?>

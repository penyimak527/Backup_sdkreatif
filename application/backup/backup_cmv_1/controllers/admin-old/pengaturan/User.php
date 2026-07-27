<?php
class User extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/pengaturan/M_user', 'model');

	}

	public function index()
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}

		$data['level'] = $this->db->get('level')->result_array();
		$data['pegawai'] = $this->db->get('pegawai')->result_array();
		$data['title'] = 'User';
		$this->load->view('template/header', $data);
		$this->load->view('admin/pengaturan/user', $data);
		$this->load->view('template/footer');
	}

	public function view($id_user)
	{
		if ($this->session->userdata('admin')['username'] == null) {
			redirect('/');
		}
		$user = $this->db->get_where('user', ['id' => $id_user])->row_array();


		$data['level'] = $this->db->get('level')->result_array();
		$data['pegawai'] = $this->db->get('pegawai')->result_array();
		$data['title'] = 'Pegawai';
		$data['id_user'] = $id_user;
		$data['user'] = $user;
		$this->load->view('template/header', $data);
		$this->load->view('admin/pengaturan/view/user', $data);
		$this->load->view('template/footer');
	}

	public function user_result()
	{
		$data = $this->model->user_result();

		echo json_encode($data);
	}
	public function user_edit()
	{
		$data = $this->model->user_edit();

		echo json_encode($data);
	}
	public function tambah()
	{
		$data = $this->model->tambah();

		echo json_encode($data);
	}
	public function edit()
	{
		$data = $this->model->edit();

		echo json_encode($data);
	}

	public function hapus()
	{
		$data = $this->model->hapus();

		echo json_encode($data);
	}

}
?>

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

		$data['level'] = $this->db->where('id !=', 8)->get('level')->result_array();
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
		$user = $this->model->user_edit($id_user);
		if (!$user) show_404();


		$data['level'] = $this->db->where('id !=', 8)->get('level')->result_array();
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
		$search = $this->input->post('search');
		$data = $this->model->user_result($search);

		echo json_encode($data);
	}
	public function user_edit()
	{ $id_user = $this->input->post('id_user');
		$data = $this->model->user_edit($id_user);

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

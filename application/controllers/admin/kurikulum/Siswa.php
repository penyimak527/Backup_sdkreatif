<?php
class Siswa extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('admin/kurikulum/M_siswa', 'model');
		$this->load->library('web_guard');
		$this->web_guard->enforce_login($this->router->fetch_method() !== 'index');
	}

	public function index()
	{
		redirect('admin/master/siswa', 'location', 301);
	}

	public function siswa_result()
	{
		$this->legacy_disabled();
	}
	public function tambah()
	{
		$this->legacy_disabled();
	}
	public function edit()
	{
		$this->legacy_disabled();
	}

	public function hapus()
	{
		$this->legacy_disabled();
	}

	private function legacy_disabled()
	{
		return $this->web_guard->respond_json(
			false,
			'Endpoint CRUD Siswa Kurikulum telah dinonaktifkan. Gunakan Master Data > Siswa.',
			['redirect' => base_url('admin/master/siswa')],
			410
		);
	}

}
?>

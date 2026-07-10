<?php
class Setting_presensi extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('admin/presensi/M_setting_presensi_pegawai', 'model');
    }
    public function index(){
        $data['title'] = 'Pengaturan Presensi';
		$this->load->view('template/header', $data);
		$this->load->view('admin/presensi/setting_presensi', $data);
		$this->load->view('template/footer');
    }
    public function view($id_setting)
    {
        if ($this->session->userdata('admin')['username'] == null) {
            redirect('/');
        }
        $data['data_row'] = $this->db->get_where('presensi_setting', ['id' => $id_setting])->row_array();
        $data['title'] = 'Pengaturan Presensi';
        $data['id'] = $id_setting;
        $this->load->view('template/header', $data);
        $this->load->view('admin/presensi/view/setting_presensi', $data);
        $this->load->view('template/footer');
    }
    public function tambah(){
		$data = $this->model->tambah();
		echo json_encode($data);
	}
    public function edit(){
        $data = $this->model->edit();
        echo json_encode($data);
    }
    public function hapus()
    {
        $data = $this->model->hapus();

        echo json_encode($data);
    }
    public function result_data()
    {
        $data = $this->model->get_setting_presensi();
        echo json_encode($data);
    }
    public function result_data_aktif()
    {
        $data = $this->model->get_setting_presensi_aktif();
        echo json_encode($data);
    }
}
?>
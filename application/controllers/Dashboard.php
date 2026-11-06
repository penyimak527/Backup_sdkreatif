<?php
class Dashboard extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->model('M_dashboard', 'model');
		// Login Dashboard AJAX menggunakan session Admin existing (tanpa library web_guard).
		// Seperti sebelumnya, pemeriksaan ini berlaku pada endpoint selain index.
		$method = $this->router->fetch_method();
		if ($method !== 'index') {
			$admin = $this->session->userdata('admin');
			if (!is_array($admin) || empty($admin['id_user']) || empty($admin['username'])) {
				if (in_array($method, array('spp_dashboard_filters', 'spp_dashboard_result'), true)) {
					$this->output->set_status_header(401)
						->set_content_type('application/json', 'utf-8')
						->set_output(json_encode(array('result'=>'false', 'message'=>'Sesi login telah berakhir. Silakan login kembali.')))
						->_display();
					exit;
				}
				redirect('/');
				exit;
			}
		}
	}

	public function index()
	{
		$data['title'] = 'Dashboard';
		$data['total_mapel'] = $this->db->get('master_mata_pelajaran')->num_rows();

		$data['total_kelas'] = $this->db->get('kelas')->num_rows();
		$data['total_pegawai'] = $this->db->get('pegawai')->num_rows();
		$data['spp_dashboard_akses'] = $this->spp_dashboard_akses();
		$this->load->view('template/header', $data);
		$this->load->view('dashboard', $data);
		$this->load->view('template/footer');
	}

	public function mapel_result()
	{
		$data = $this->model->mapel_result();

		echo json_encode($data);
	}
	public function dashboard_result()
	{
		$data = $this->model->dashboard_result();

		echo json_encode($data);
	}
	public function jadwal_result()
	{
		$data = $this->model->jadwal_result();

		echo json_encode($data);
	}
	public function saldo_result()
	{
		
		$tanggal_awal = $this->input->post('tanggal_awal');
		$tanggal_akhir = $this->input->post('tanggal_akhir');
		$data = $this->model->dashboard_keuangan($tanggal_awal,$tanggal_akhir);

		echo json_encode($data);
	}
public function grafik_perbandingan()
{
	$data = $this->model->grafik_perbandingan();

		echo json_encode($data);
    
}
	public function grafik_rencana_pemasukan_result()
	{
		$data = $this->model->rencana_perbandingan_pemasukan_result();

		echo json_encode($data);
	}
	public function grafik_rencana_pengeluaran_result()
	{
		$data = $this->model->rencana_perbandingan_pengeluaran_result();

		echo json_encode($data);
	}
	public function pengeluaran_terbesar_result()
	{
		$data = $this->model->top_pengeluaran_result();

		echo json_encode($data);
	}
	public function notif_keuangan()
	{
		$data = $this->model->notif_keuangan();

		echo json_encode($data);
	}
	public function api_jadwal_result()
	{
		$data = $this->model->api_jadwal_result();

		echo json_encode($data);
	}


    // Hak baca ringkasan: mengikuti conf_list_menu yang sudah digunakan modul SPP.
    // Tidak menambah menu, level, izin aksi, atau aturan akses modul lain.
    private function spp_dashboard_akses()
    {
        $admin = $this->session->userdata('admin');
        
        $multi_level_session = $admin;
        $multi_level_values = is_array($multi_level_session) ? (
            !empty($multi_level_session['id_levels']) && is_array($multi_level_session['id_levels'])
                ? $multi_level_session['id_levels'] : explode(',', (string) ($multi_level_session['id_level'] ?? ''))
        ) : array();
        $levels = array_values(array_unique(array_map('intval', array_filter($multi_level_values, static function ($value) {
            $value = trim((string) $value);
            return $value !== '' && ctype_digit($value) && (int) $value > 0;
        }))));
        if (!$levels) return array('tagihan'=>false, 'tabungan'=>false);
        $groups = $this->db->select('`group`', false)
            ->where_in('id_level', $levels)
            ->where_in('group', array('Tagihan & Pembayaran', 'Tabungan'))
            ->get('conf_list_menu')->result_array();
        $names = array_column($groups, 'group');
        return array('tagihan'=>in_array('Tagihan & Pembayaran', $names, true), 'tabungan'=>in_array('Tabungan', $names, true));
    }

    private function spp_dashboard_guard()
    {
        $access = $this->spp_dashboard_akses();
        if (!$access['tagihan'] && !$access['tabungan']) {
            show_error('Dashboard SPP tidak diizinkan untuk level pengguna ini.', 403);
        }
        return $access;
    }

    public function spp_dashboard_filters()
    {
        $access = $this->spp_dashboard_guard();
        $data = $this->model->spp_filters();
        if (!$access['tagihan']) $data['jenis'] = array();
        $data['akses'] = $access;
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('result'=>'true', 'data'=>$data)));
    }

    public function spp_dashboard_result()
    {
        $access = $this->spp_dashboard_guard();
        $meta = $this->model->spp_filters();
        $validPeriods = array_map('intval', array_column($meta['periode'], 'id'));
        $period = (int)$this->input->post('periode');
        if ($period <= 0 || !in_array($period, $validPeriods, true)) $period = (int)$meta['active'];
        $kelas = (int)$this->input->post('kelas');
        if ($kelas > 0) {
            $found = false;
            foreach ($meta['kelas'] as $k) {
                if ((int)$k['id'] === $kelas && (int)$k['id_periode'] === $period) { $found = true; break; }
            }
            if (!$found) $kelas = 0;
        }
        $tingkat = (int)$this->input->post('tingkat');
        if ($tingkat < 1 || $tingkat > 6) $tingkat = 0;
        // 0 = Seluruh Tahun Ajaran; YYYYMM = kumulatif Juli sampai bulan terpilih.
        // Validasi terhadap tahun pelajaran agar request AJAX tidak dapat melampaui periode.
        $tahunMulai = 0;
        foreach ($meta['periode'] as $p) {
            if ((int)$p['id'] !== $period) continue;
            if (preg_match('/(20\d{2})\s*[\/-]\s*(20\d{2})/', (string)$p['periode'], $m)
                && (int)$m[2] === (int)$m[1] + 1) {
                $tahunMulai = (int)$m[1];
            }
            break;
        }
        $bulan = (int)$this->input->post('bulan');
        if ($tahunMulai <= 0 || ($bulan !== 0 && (
            $bulan < ($tahunMulai * 100 + 7) ||
            $bulan > (($tahunMulai + 1) * 100 + 6) ||
            ($bulan % 100) < 1 || ($bulan % 100) > 12
        ))) $bulan = 0;
        $jenis = (int)$this->input->post('jenis');
        if ($jenis > 0 && !in_array($jenis, array_map('intval',array_column($meta['jenis'],'id')), true)) $jenis = 0;
        $filter = array('periode'=>$period,'active'=>(int)$meta['active'], 'kelas'=>$kelas,'tingkat'=>$tingkat,'bulan'=>$bulan,'tahun_mulai'=>$tahunMulai,'jenis'=>$jenis);
        $data = $this->model->spp_summary($filter, $access['tagihan'], $access['tabungan']);
        $this->output->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(array('result'=>'true','data'=>$data)));
    }


}
?>

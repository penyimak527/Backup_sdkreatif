<?php

class M_hitung_gaji extends CI_Model
{
	public function hitung_gaji_result($search = null, $bulan = null, $tahun = null)
	{
		$hari_kerja = 15;

		$this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id as id_gaji, b.gaji_pokok, 
		b.struktural, b.tunjangan_pendidikan, b.wali_kelas');
		$this->db->from('pegawai a');
		$this->db->join('gaji b', 'a.id = b.id_pegawai', 'left');
		// $this->db->join('potongan_pegawai c', 'a.id = c.id_pegawai', 'left');

		if ($search != null) {
			$this->db->like('a.nama_pegawai', $search);
		}
		$pegawai = $this->db->get()->result_array();

		$result = [];
		foreach ($pegawai as $item) {
			$jumlah_hadir = $this->db->query("SELECT COUNT(*) AS jumlah_hadir FROM presensi_pegawai WHERE id_pegawai = ? AND MONTH(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ? AND YEAR(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ?", array($item['id_pegawai'], $bulan, $tahun))->row()->jumlah_hadir;
			$jumlah_ijin = $this->db->query("SELECT COUNT(*) AS jumlah_ijin FROM izin_pegawai
    		WHERE id_pegawai = ? AND status_approval = 1 AND MONTH(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ? 
    		AND YEAR(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ?", [$item['id_pegawai'], $bulan, $tahun])->row()->jumlah_ijin;
			$jumlah_tidak_hadir = $hari_kerja - $jumlah_hadir;
			if ($jumlah_tidak_hadir < 0) {
				$jumlah_tidak_hadir = 0;
			}
			$jumlah_alfa = $jumlah_tidak_hadir - $jumlah_ijin;

			if ($jumlah_alfa < 0) {
				$jumlah_alfa = 0;
			}

			$potongan_detail = $this->db->query("SELECT mp.nama_potongan, ppd.nominal
				FROM pegawai_potongan pp
				JOIN pegawai_potongan_detail ppd ON pp.id = ppd.id_pegawai_potongan
				JOIN master_potongan mp ON mp.id = ppd.id_master_potongan
				WHERE pp.id_pegawai = ? AND pp.bulan = ? AND pp.tahun = ?", [$item['id_pegawai'], $bulan, $tahun])->result_array();

			$total_potongan_lain = array_sum(array_column($potongan_detail, 'nominal'));
			$bonus = $this->db->select_sum('nominal')->from('bonus')->where('id_pegawai', $item['id_pegawai'])->where('bulan', $bulan)->where('tahun', $tahun)->get()->row_array();

			$pinjaman = $this->db->query("SELECT pd.nominal_tagihan FROM pinjaman pj LEFT JOIN pinjaman_detail pd on pj.id = pd.id_pinjaman WHERE 
			pj.id_pegawai = ? AND pd.bulan = ? AND pd.tahun = ? AND pd.status_bayar = 'Belum'", [$item['id_pegawai'], $bulan, $tahun])->row_array();
			$sisa_pinjaman = $this->db->query("SELECT pj.sisa_pinjaman FROM pinjaman pj WHERE pj.id_pegawai = ? ", [$item['id_pegawai']])->row_array();

			if ($jumlah_tidak_hadir < 0) {
				$jumlah_tidak_hadir = 0;
			}
			$gaji_pokok = (int) $item['gaji_pokok'];
			$struktural = (int) $item['struktural'];
			$tunjangan_pendidikan = (int) $item['tunjangan_pendidikan'];
			$wali_kelas = (int) $item['wali_kelas'];

			$bonus = (int) ($bonus['nominal'] ?? 0);

			$total_pendapatan = $gaji_pokok + $struktural + $tunjangan_pendidikan + $wali_kelas;
			$pendapatan_total = $total_pendapatan + $bonus;

			// $potongan_tidak_hadir = ($total_pendapatan * 5 / 100) * $jumlah_tidak_hadir;
			$rumus_potongan = $this->get_rumus_potongan();

			$persen_tidak_hadir = $rumus_potongan['tidak_hadir'];
			$persen_uig_uik = $rumus_potongan['uig_uik'];
			$persen_zakat = $rumus_potongan['zakat'];

			$potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_alfa;
			$uig_uik = $total_pendapatan * $persen_uig_uik / 100;
			$zakat = $total_pendapatan * $persen_zakat / 100;
			// $potongan_tidak_hadir = ($total_pendapatan * 5 / 100) * $jumlah_alfa;
			// $uig_uik = $total_pendapatan * 1 / 100;
			// $zakat = $total_pendapatan * 1.5 / 100;

			$potongan_pinjaman = (int) ($pinjaman['nominal_tagihan'] ?? 0);
			$s_pinjaman = (int) ($sisa_pinjaman['sisa_pinjaman'] ?? 0);

			$total_pengeluaran = $potongan_tidak_hadir + $uig_uik + $zakat + $total_potongan_lain + $potongan_pinjaman;
			$gaji_bersih = $total_pendapatan - $total_pengeluaran;
			$gaji_bersih_total = $gaji_bersih + $bonus;

			$cek_penggajian = $this->db->get_where('penggajian', [
				'id_pegawai' => $item['id_pegawai'],
				'bulan' => $bulan,
				'tahun' => $tahun
			])->row_array();

			$status_penggajian = $cek_penggajian ? 'Sudah Dihitung' : 'Belum Dihitung';

			if ($cek_penggajian) {
				$potongan_detail = $this->db->query("SELECT mp.nama_potongan, pp.nominal
        			FROM penggajian_potongan pp
        			JOIN master_potongan mp ON mp.id = pp.id_master_potongan
        			WHERE pp.id_penggajian = ? ", [$cek_penggajian['id']])->result_array();
				$data_penggajian = $cek_penggajian;
				$data_penggajian['potongan_detail'] = $potongan_detail;
				$data_penggajian['total_pendapatan'] = $cek_penggajian['total_pendapatan'] + ($cek_penggajian['total_bonus'] ?? 0);
				$data_penggajian['sisa_pinjaman'] = $s_pinjaman;
				// 'total_pendapatan' => $data_penggajian['total_pendapatan'] + ($data_penggajian['total_bonus'] ?? 0),
			} else {
				$data_penggajian = [
					'jumlah_hadir' => $jumlah_hadir,
					'jumlah_tidak_hadir' => $jumlah_tidak_hadir,
					'jumlah_ijin' => $jumlah_ijin,
					'jumlah_alfa' => $jumlah_alfa,

					'gaji_pokok' => $gaji_pokok,
					'struktural' => $struktural,
					'tunjangan_pendidikan' => $tunjangan_pendidikan,
					'wali_kelas' => $wali_kelas,
					'total_bonus' => $bonus,

					'total_pendapatan' => $pendapatan_total,
					'total_pendapatan_tetap' => $total_pendapatan,

					'potongan_tidak_hadir' => $potongan_tidak_hadir,
					'uig_uik' => $uig_uik,
					'zakat' => $zakat,
					'potongan_detail' => $potongan_detail,
					'total_potongan_lain' => $total_potongan_lain,
					'cicilan_pinjaman' => $potongan_pinjaman,
					'sisa_pinjaman' => $s_pinjaman,

					'total_pengeluaran' => $total_pengeluaran,
					'gaji_bersih' => $gaji_bersih_total,
					'gaji_bersih_tetap' => $gaji_bersih,

					'persen_potongan_tidak_hadir' => $persen_tidak_hadir,
					'persen_uig_uik' => $persen_uig_uik,
					'persen_zakat' => $persen_zakat,
				];
			}

			$result[] = [
				'id_pegawai' => $item['id_pegawai'],
				'nama_pegawai' => $item['nama_pegawai'],
				'bulan' => $bulan,
				'tahun' => $tahun,

				'gaji_pokok' => $data_penggajian['gaji_pokok'],
				'struktural' => $data_penggajian['struktural'],
				'tunjangan_pendidikan' => $data_penggajian['tunjangan_pendidikan'],
				'wali_kelas' => $data_penggajian['wali_kelas'],
				'total_bonus' => $data_penggajian['total_bonus'] ?? 0,
				// 'total_pendapatan' => $data_penggajian['total_pendapatan'] + ($data_penggajian['total_bonus'] ?? 0),
				'total_pendapatan' => $data_penggajian['total_pendapatan'],
				'jumlah_hadir' => $data_penggajian['jumlah_hadir'],
				'jumlah_tidak_hadir' => $data_penggajian['jumlah_tidak_hadir'],
				'jumlah_ijin' => $data_penggajian['jumlah_ijin'] ?? 0,
				'jumlah_alfa' => $data_penggajian['jumlah_alfa'] ?? 0,
				'potongan_tidak_hadir' => $data_penggajian['potongan_tidak_hadir'],
				'uig_uik' => $data_penggajian['uig_uik'],
				'zakat' => $data_penggajian['zakat'],
				'potongan_detail' => $data_penggajian['potongan_detail'] ?? [],
				'total_potongan_lain' => $data_penggajian['total_potongan_lain'] ?? 0,
				'cicilan_pinjaman' => $data_penggajian['cicilan_pinjaman'] ?? 0,
				'sisa_pinjaman' => $data_penggajian['sisa_pinjaman'] ?? 0,
				'total_pengeluaran' => $data_penggajian['total_pengeluaran'],
				'gaji_bersih' => $data_penggajian['gaji_bersih'],
				'status_penggajian' => $status_penggajian,

				'persen_potongan_tidak_hadir' => $data_penggajian['persen_potongan_tidak_hadir'],
				'persen_uig_uik' => $data_penggajian['persen_uig_uik'],
				'persen_zakat' => $data_penggajian['persen_zakat'],
			];
		}
		return $result;
	}

	public function hitung()
	{
		$id_pegawai = $this->input->post('id');
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');

		$this->proses_penggajian($id_pegawai, $bulan, $tahun);
		// $this->update_pengeluaran_penggajian($bulan, $tahun);
		return true;
	}

	public function hitung_semua()
	{
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');

		$pegawai = $this->db->get('pegawai')->result_array();
		foreach ($pegawai as $p) {
			$this->proses_penggajian(
				$p['id'],
				$bulan,
				$tahun
			);
		}
		// $this->update_pengeluaran_penggajian($bulan, $tahun);
		return true;
	}
	private function proses_penggajian($id_pegawai, $bulan, $tahun)
	{
		$hari_kerja = 15;
		$this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id AS id_gaji,
        b.gaji_pokok, b.struktural, b.tunjangan_pendidikan, b.wali_kelas,
    ');

		$this->db->from('pegawai a');
		$this->db->join('gaji b', 'a.id = b.id_pegawai', 'left');
		// $this->db->join('potongan_pegawai c', 'a.id = c.id_pegawai', 'left');
		$this->db->where('a.id', $id_pegawai);
		$item = $this->db->get()->row_array();

		if (!$item) {
			return false;
		}

		// jumlah hadir
		$jumlah_hadir = $this->db->query("SELECT COUNT(*) AS jumlah_hadir FROM presensi_pegawai WHERE id_pegawai = ? AND MONTH(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ? AND YEAR(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ?", array($id_pegawai, $bulan, $tahun))->row()->jumlah_hadir;
		$jumlah_ijin = $this->db->query("SELECT COUNT(*) AS jumlah_ijin FROM izin_pegawai
    		WHERE id_pegawai = ? AND status_approval = 1 AND MONTH(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ? 
    		AND YEAR(STR_TO_DATE(tgl_tidak_hadir, '%d-%m-%Y')) = ?", array($id_pegawai, $bulan, $tahun))->row()->jumlah_ijin;

		$potongan_detail = $this->db->query("SELECT mp.nama_potongan, ppd.nominal FROM pegawai_potongan pp
JOIN pegawai_potongan_detail ppd ON pp.id = ppd.id_pegawai_potongan JOIN master_potongan mp ON mp.id = ppd.id_master_potongan
WHERE pp.id_pegawai = ? AND pp.bulan = ? AND pp.tahun = ?", [$item['id_pegawai'], $bulan, $tahun])->result_array();

		$total_potongan_lain = array_sum(array_column($potongan_detail, 'nominal'));

		$bonus = $this->db
			->select_sum('nominal')
			->from('bonus')
			->where('id_pegawai', $item['id_pegawai'])
			->where('bulan', $bulan)
			->where('tahun', $tahun)
			->get()
			->row_array();

		$queryPinjaman = $this->db->query("
    SELECT
        pd.id,
        pd.id_pinjaman,
        pd.nominal_tagihan,
        pj.sisa_pinjaman
    FROM pinjaman pj
    LEFT JOIN pinjaman_detail pd
        ON pj.id = pd.id_pinjaman
    WHERE
        pj.id_pegawai = ?
        AND pd.bulan = ?
        AND pd.tahun = ?
        AND pd.status_bayar='Belum'
", [
			$item['id_pegawai'],
			$bulan,
			$tahun
		]);

		$pinjaman = $queryPinjaman->row_array();

		$id_detail_pinjaman = null;
		$id_pinjaman = null;
		$potongan_pinjaman = 0;
		$sisa_pinjaman = 0;

		if ($queryPinjaman->num_rows() > 0) {
			$id_detail_pinjaman = $pinjaman['id'];
			$id_pinjaman = $pinjaman['id_pinjaman'];
			$potongan_pinjaman = (int) $pinjaman['nominal_tagihan'];
			$sisa_pinjaman = (int) $pinjaman['sisa_pinjaman'];
		}

		$jumlah_tidak_hadir = $hari_kerja - $jumlah_hadir;
		if ($jumlah_tidak_hadir < 0) {
			$jumlah_tidak_hadir = 0;
		}
		$jumlah_alfa = $jumlah_tidak_hadir - $jumlah_ijin;
		if ($jumlah_alfa < 0) {
			$jumlah_alfa = 0;
		}
		// pendapatan
		$gaji_pokok = (int) $item['gaji_pokok'];
		$struktural = (int) $item['struktural'];
		$tunjangan_pendidikan = (int) $item['tunjangan_pendidikan'];
		$wali_kelas = (int) $item['wali_kelas'];
		$bonus = (int) ($bonus['nominal'] ?? 0);
		$total_pendapatan = $gaji_pokok + $struktural + $tunjangan_pendidikan + $wali_kelas;
		// $pendapatan_total = $total_pendapatan + $bonus;

		// potongan
		// $potongan_tidak_hadir = ($total_pendapatan * 5 / 100) * $jumlah_tidak_hadir;
		// $potongan_tidak_hadir = ($total_pendapatan * 5 / 100) * $jumlah_alfa;
		// $uig_uik = $total_pendapatan * 1 / 100;
		// $zakat = $total_pendapatan * 1.5 / 100;
		$rumus_potongan = $this->get_rumus_potongan();

		$persen_tidak_hadir = $rumus_potongan['tidak_hadir'];
		$persen_uig_uik = $rumus_potongan['uig_uik'];
		$persen_zakat = $rumus_potongan['zakat'];

		$potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_alfa;
		$uig_uik = $total_pendapatan * $persen_uig_uik / 100;
		$zakat = $total_pendapatan * $persen_zakat / 100;

		$total_pengeluaran = $potongan_tidak_hadir + $uig_uik + $zakat + $total_potongan_lain + $potongan_pinjaman;

		$gaji_bersih = $total_pendapatan - $total_pengeluaran;
		$gaji_bersih_total = $gaji_bersih + $bonus;

		$data = [
			'id_pegawai' => $id_pegawai,
			'id_gaji' => $item['id_gaji'],
			'bulan' => $bulan,
			'tahun' => $tahun,

			'jumlah_hadir' => $jumlah_hadir,
			'jumlah_tidak_hadir' => $jumlah_tidak_hadir,
			'jumlah_ijin' => $jumlah_ijin,
			'jumlah_alfa' => $jumlah_alfa,

			'gaji_pokok' => $gaji_pokok,
			'struktural' => $struktural,
			'tunjangan_pendidikan' => $tunjangan_pendidikan,
			'wali_kelas' => $wali_kelas,
			'total_bonus' => $bonus,

			'total_pendapatan' => $total_pendapatan,

			'potongan_tidak_hadir' => $potongan_tidak_hadir,
			'uig_uik' => $uig_uik,
			'zakat' => $zakat,
			'total_potongan_lain' => $total_potongan_lain,
			'cicilan_pinjaman' => $potongan_pinjaman,
			'total_pengeluaran' => $total_pengeluaran,
			'gaji_bersih' => $gaji_bersih_total,

			'persen_potongan_tidak_hadir' => $persen_tidak_hadir,
			'persen_uig_uik' => $persen_uig_uik,
			'persen_zakat' => $persen_zakat,

			'tanggal_generates' => date('d-m-Y')
		];

		// cek data
		$cek = $this->db->get_where('penggajian', [
			'id_pegawai' => $id_pegawai,
			'bulan' => $bulan,
			'tahun' => $tahun
		])->row_array();


		// if ($cek) {
		// 	$this->db->where('id', $cek['id']);
		// 	return $this->db->update('penggajian', $data);
		// } else {
		// 	return $this->db->insert('penggajian', $data);
		// }

		if ($cek) {
			return true;
			// $id_penggajian = $cek['id'];

			// $this->db->where('id_penggajian', $id_penggajian);
			// $this->db->delete('penggajian_potongan');

			// $this->db->where('id', $id_penggajian);
			// $simpan = $this->db->update('penggajian', $data);
			// $this->db->where('id', $cek['id']);
			// $simpan = $this->db->update('penggajian', $data);
		}
		// $simpan = $this->db->insert('penggajian', $data);
		$simpan = $this->db->insert('penggajian', $data);
		$id_penggajian = $this->db->insert_id();


		foreach ($potongan_detail as $potongan) {
			$master = $this->db->get_where('master_potongan', ['nama_potongan' => $potongan['nama_potongan']])->row_array();
			$this->db->insert('penggajian_potongan', ['id_penggajian' => $id_penggajian, 'id_master_potongan' => $master['id'], 'nominal' => $potongan['nominal']]);
		}
		if ($simpan && $id_detail_pinjaman != null) {
			// update detail pinjaman
			$this->db
				->where('id', $id_detail_pinjaman)
				->update('pinjaman_detail', [
					'nominal_bayar' => $potongan_pinjaman,
					'status_bayar' => 'Sudah',
					'tanggal_bayar' => date('d-m-Y')
				]);

			// hitung sisa pinjaman
			$sisa_baru = $sisa_pinjaman - $potongan_pinjaman;

			if ($sisa_baru < 0) {
				$sisa_baru = 0;
			}

			$dataMaster = [
				'sisa_pinjaman' => $sisa_baru
			];

			// jika lunas
			if ($sisa_baru == 0) {
				$dataMaster['status'] = 'Lunas';
			}
			$this->db->where('id', $id_pinjaman)->update('pinjaman', $dataMaster);
		}

		return $simpan;
	}

	// private function update_pengeluaran_penggajian($bulan, $tahun)
	// {
	// 	$this->db->select_sum('gaji_bersih');
	// 	$this->db->where('bulan', $bulan);
	// 	$this->db->where('tahun', $tahun);
	// 	$total = $this->db->get('penggajian')->row()->gaji_bersih;

	// 	if (!$total) {
	// 		$total = 0;
	// 	}

	// 	$nama_bulan = [
	// 		'01' => 'Januari',
	// 		'02' => 'Februari',
	// 		'03' => 'Maret',
	// 		'04' => 'April',
	// 		'05' => 'Mei',
	// 		'06' => 'Juni',
	// 		'07' => 'Juli',
	// 		'08' => 'Agustus',
	// 		'09' => 'September',
	// 		'10' => 'Oktober',
	// 		'11' => 'November',
	// 		'12' => 'Desember'
	// 	];
	// 	$cek = $this->db->query("SELECT a.* FROM pengeluaran a LEFT JOIN kode_akun b on a.id_kode_akun = b.id WHERE b.keterangan ='Gaji' 
	// 	AND a.bulan = ? AND a.tahun = ?", [$bulan, $tahun])->row_array();

	// 	$kode_akun = $this->db->get_where('kode_akun', ['jenis' => 'Pengeluaran', 'keterangan' => 'Gaji'])->row_array();

	// 	// $tanggal_input = date('t-m-Y', strtotime($tahun . '-' . $bulan . '-01'));
	// 	$tanggal_input = date('d-m-Y');
	// 	$id_pegawai = $this->session->userdata('admin')['id_pegawai'];
	// 	$sumber_dana = $this->db->get_where('kasbank', ['keterangan' => 'Kas Lembaga'])->row_array();
	// 	$data = [
	// 		'id_kode_akun' => $kode_akun['id'],
	// 		'id_pegawai' => $id_pegawai,
	// 		'keterangan' => 'Penggajian Bulan ' . $nama_bulan[$bulan] . ' ' . $tahun,
	// 		'jumlah' => $total,
	// 		'tanggal_input' => $tanggal_input,
	// 		'bulan' => $bulan,
	// 		'tahun' => $tahun,
	// 		'sumber_dana' => $sumber_dana['id']
	// 	];

	// 	if ($cek) {
	// 		$this->db->where('id', $cek['id']);
	// 		$this->db->update('pengeluaran', $data);
	// 	} else {
	// 		$data['tanggal'] = date('d-m-Y');
	// 		$this->db->insert('pengeluaran', $data);
	// 	}
	// }

	public function get_potongan_gaji()
	{
		$get_potongan_gaji = $this->db->query("SELECT * FROM rumus_potongan WHERE nama_potongan <> 'gaji'");
		return $get_potongan_gaji->result_array();
	}
	public function edit_potongan_gaji()
	{
		$potongan_tidak_hadir = (float) str_replace(',', '.', $this->input->post('potongan_tidak_masuk'));
		$uig_uik = (float) str_replace(',', '.', $this->input->post('uig_uik'));
		$zakat = (float) str_replace(',', '.', $this->input->post('zakat'));

		$this->db->trans_begin();

		$this->simpan_rumus_potongan('zakat', $zakat);
		$this->simpan_rumus_potongan('uig_uik', $uig_uik);
		$this->simpan_rumus_potongan('tidak_hadir', $potongan_tidak_hadir);

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();

			$response = array(
				'status' => false,
				'code' => 401,
				'message' => 'Gagal menyimpan data'
			);
		} else {
			$this->db->trans_commit();

			$response = array(
				'status' => true,
				'code' => 200,
				'message' => 'Data berhasil disimpan'
			);
		}

		return $response;
	}

	private function simpan_rumus_potongan($nama_potongan, $nominal_persen)
	{
		$cek = $this->db
			->where('nama_potongan', $nama_potongan)
			->get('rumus_potongan')
			->row_array();

		if ($cek) {
			$this->db
				->where('nama_potongan', $nama_potongan)
				->update('rumus_potongan', [
					'nominal_persen' => $nominal_persen
				]);
		} else {
			$this->db->insert('rumus_potongan', [
				'nama_potongan' => $nama_potongan,
				'nominal_persen' => $nominal_persen
			]);
		}
	}

	private function get_rumus_potongan()
	{
		$rows = $this->db
			->select('nama_potongan, nominal_persen')
			->from('rumus_potongan')
			->where('nama_potongan !=', 'gaji')
			->get()
			->result_array();

		$result = [
			'zakat' => 0,
			'uig_uik' => 0,
			'tidak_hadir' => 0,
		];

		foreach ($rows as $row) {
			$nama = $row['nama_potongan'];
			$result[$nama] = (float) $row['nominal_persen'];
		}

		return $result;
	}
}
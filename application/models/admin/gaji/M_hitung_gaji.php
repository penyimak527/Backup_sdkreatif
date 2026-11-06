<?php

class M_hitung_gaji extends CI_Model
{
	public function hitung_gaji_result($search = null, $bulan = null, $tahun = null)
	{
		if (empty($bulan) || empty($tahun)) {
			return [];
		}

		$hari_kerja = $this->get_hari_efektif($bulan, $tahun);
		if ($hari_kerja === null) {
			return [];
		}

		$this->db->select('a.id as id_pegawai, a.nama_pegawai, b.id as id_gaji, b.gaji_pokok, 
		b.struktural, b.tunjangan_pendidikan, b.wali_kelas');
		$this->db->from('pegawai a');
		$this->db->join('gaji b', 'a.id = b.id_pegawai', 'inner');
		// $this->db->join('potongan_pegawai c', 'a.id = c.id_pegawai', 'left');

		if ($search != null) {
			$this->db->like('a.nama_pegawai', $search);
		}
		$pegawai = $this->db->get()->result_array();

		$result = [];
		foreach ($pegawai as $item) {
			$rekap_kehadiran = $this->hitung_izin_dan_alfa($item['id_pegawai'], $bulan, $tahun, $hari_kerja);
			$jumlah_hadir = $rekap_kehadiran['jumlah_hadir'];
			$jumlah_tidak_hadir = $rekap_kehadiran['jumlah_tidak_hadir'];
			$jumlah_ijin = $rekap_kehadiran['jumlah_ijin'];
			$jumlah_ijin_pribadi = $rekap_kehadiran['jumlah_ijin_pribadi'];
			$jumlah_alfa = $rekap_kehadiran['jumlah_alfa'];
			$jumlah_hari_dipotong = $rekap_kehadiran['jumlah_hari_dipotong'];

			$potongan_detail = $this->db->query("SELECT mp.nama_potongan, ppd.keterangan_potongan, ppd.nominal
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

			$rumus_potongan = $this->get_rumus_potongan();

			$persen_tidak_hadir = $rumus_potongan['tidak_hadir'];
			$persen_uig_uik = $rumus_potongan['uig_uik'];
			$persen_zakat = $rumus_potongan['zakat'];

			// $potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_alfa;
			$potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_hari_dipotong;

			$uig_uik = $total_pendapatan * $persen_uig_uik / 100;
			$zakat = $total_pendapatan * $persen_zakat / 100;

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
				$potongan_detail = $this->db->query("SELECT mp.nama_potongan, pp.keterangan_potongan, pp.nominal
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
					'jumlah_ijin_pribadi' => $jumlah_ijin_pribadi,
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
				'jumlah_ijin_pribadi' => $data_penggajian['jumlah_ijin_pribadi'] ?? 0,
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

		if (empty($bulan) || empty($tahun) || $this->get_hari_efektif($bulan, $tahun) === null) {
			return false;
		}

		$hasil = $this->proses_penggajian($id_pegawai, $bulan, $tahun);
		if ($hasil === true) {
			$this->tambah_riwayat_hitung_gaji('hitung_satu', $bulan, $tahun);
		}
		return $hasil;
	}

	public function hitung_semua()
	{
		$bulan = $this->input->post('bulan');
		$tahun = $this->input->post('tahun');

		if (empty($bulan) || empty($tahun) || $this->get_hari_efektif($bulan, $tahun) === null) {
			return false;
		}

		$pegawai = $this->db->get('gaji')->result_array();
		$semua_berhasil = true;
		foreach ($pegawai as $p) {
			$hasil = $this->proses_penggajian(
				$p['id_pegawai'],
				$bulan,
				$tahun
			);
			if ($hasil !== true) {
				$semua_berhasil = false;
			}
		}
		if ($semua_berhasil === true) {
			$this->tambah_riwayat_hitung_gaji('hitung_semua',$bulan,$tahun);
		}
		return $semua_berhasil;
	}
	private function tambah_riwayat_hitung_gaji($jenis_hitung,$bulan,$tahun) {
			$id_pegawai = $this->session->userdata('admin')['id_pegawai'] ?? '';
			$data = [
			'id_pegawai' => $id_pegawai,
			'jenis_hitung' => $jenis_hitung,
			'bulan' => $bulan,
			'tahun' => $tahun,
			'tanggal' => date('d-m-Y'),
			'waktu' => date('H:i:s')
		];

		return $this->db->insert(
			'riwayat_hitung_gaji',
			$data
		);
	}

	/**
	 * Hitung izin berdasarkan tanggal yang benar-benar tidak memiliki presensi.
	 * Terlambat tidak mengurangi alfa; Pribadi tetap dipotong satu kali.
	 */
	private function hitung_izin_dan_alfa($id_pegawai, $bulan, $tahun, $hari_kerja)
	{
		$jumlah_hadir = (int) $this->db->query("SELECT COUNT(DISTINCT STR_TO_DATE(tanggal, '%d-%m-%Y')) AS jumlah_hadir
			FROM presensi_pegawai WHERE id_pegawai = ?
			AND MONTH(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ?
			AND YEAR(STR_TO_DATE(tanggal, '%d-%m-%Y')) = ?", [$id_pegawai, $bulan, $tahun])->row()->jumlah_hadir;

		// Satu tanggal izin hanya dihitung sekali dan tidak boleh beririsan dengan tanggal hadir.
		// id 1 = Pribadi, 2 = Sakit, 12 = Izin. id 13 = Terlambat tidak termasuk.
		$izin_tanpa_presensi = $this->db->query("SELECT
			MAX(CASE WHEN i.id_master_izin = 1 THEN 1 ELSE 0 END) AS izin_pribadi
			FROM izin_pegawai i
			WHERE i.id_pegawai = ? AND i.status_approval = 1
			AND i.id_master_izin IN (1, 2, 12)
			AND MONTH(STR_TO_DATE(i.tgl_tidak_hadir, '%d-%m-%Y')) = ?
			AND YEAR(STR_TO_DATE(i.tgl_tidak_hadir, '%d-%m-%Y')) = ?
			AND NOT EXISTS (
				SELECT 1 FROM presensi_pegawai p
				WHERE p.id_pegawai = i.id_pegawai
				AND STR_TO_DATE(p.tanggal, '%d-%m-%Y') = STR_TO_DATE(i.tgl_tidak_hadir, '%d-%m-%Y')
			)
			GROUP BY STR_TO_DATE(i.tgl_tidak_hadir, '%d-%m-%Y')", [$id_pegawai, $bulan, $tahun])->result_array();

		$jumlah_ijin_pribadi = 0;
		$jumlah_ijin_tidak_dipotong = 0;
		foreach ($izin_tanpa_presensi as $izin) {
			if ((int) $izin['izin_pribadi'] === 1) {
				$jumlah_ijin_pribadi++;
			} else {
				$jumlah_ijin_tidak_dipotong++;
			}
		}

		$jumlah_tidak_hadir = max(0, (int) $hari_kerja - $jumlah_hadir);
		// Izin tidak boleh melebihi selisih hari efektif dengan kehadiran.
		$jumlah_ijin_pribadi = min($jumlah_ijin_pribadi, $jumlah_tidak_hadir);
		$jumlah_ijin_tidak_dipotong = min(
			$jumlah_ijin_tidak_dipotong,
			$jumlah_tidak_hadir - $jumlah_ijin_pribadi
		);
		$jumlah_ijin = $jumlah_ijin_pribadi + $jumlah_ijin_tidak_dipotong;
		$jumlah_alfa = $jumlah_tidak_hadir - $jumlah_ijin;
		$jumlah_hari_dipotong = $jumlah_alfa + $jumlah_ijin_pribadi;

		return [
			'jumlah_hadir' => $jumlah_hadir,
			'jumlah_tidak_hadir' => $jumlah_tidak_hadir,
			'jumlah_ijin' => $jumlah_ijin,
			'jumlah_ijin_pribadi' => $jumlah_ijin_pribadi,
			'jumlah_alfa' => $jumlah_alfa,
			'jumlah_hari_dipotong' => $jumlah_hari_dipotong
		];
	}

	private function proses_penggajian($id_pegawai, $bulan, $tahun)
	{
		$hari_kerja = $this->get_hari_efektif($bulan, $tahun);
		if ($hari_kerja === null) {
			return false;
		}

		$this->db->select('
		a.id AS id_pegawai,
		a.nama_pegawai,
		b.id AS id_gaji,
		b.gaji_pokok,
		b.struktural,
		b.tunjangan_pendidikan,
		b.wali_kelas
	');
		$this->db->from('pegawai a');
		$this->db->join('gaji b', 'a.id = b.id_pegawai', 'inner');
		$this->db->where('a.id', $id_pegawai);

		$item = $this->db->get()->row_array();

		if (!$item) {
			return false;
		}

		/*
		|--------------------------------------------------------------------------
		| Cek apakah penggajian sudah pernah dihitung
		|--------------------------------------------------------------------------
		*/
		$cek_penggajian = $this->db->get_where('penggajian', [
			'id_pegawai' => $id_pegawai,
			'bulan' => $bulan,
			'tahun' => $tahun
		])->row_array();

		/*
		|--------------------------------------------------------------------------
		| Hitung presensi
		|--------------------------------------------------------------------------
		*/
		$rekap_kehadiran = $this->hitung_izin_dan_alfa($id_pegawai, $bulan, $tahun, $hari_kerja);
		$jumlah_hadir = $rekap_kehadiran['jumlah_hadir'];
		$jumlah_tidak_hadir = $rekap_kehadiran['jumlah_tidak_hadir'];
		$jumlah_ijin = $rekap_kehadiran['jumlah_ijin'];
		$jumlah_ijin_pribadi = $rekap_kehadiran['jumlah_ijin_pribadi'];
		$jumlah_alfa = $rekap_kehadiran['jumlah_alfa'];
		$jumlah_hari_dipotong = $rekap_kehadiran['jumlah_hari_dipotong'];
		/*
		|--------------------------------------------------------------------------
		| Potongan tambahan pegawai
		|--------------------------------------------------------------------------
		*/
		$potongan_detail = $this->db->query("
		SELECT
			mp.id AS id_master_potongan,
			mp.nama_potongan,
			ppd.keterangan_potongan,
			ppd.nominal
		FROM pegawai_potongan pp
		JOIN pegawai_potongan_detail ppd
			ON pp.id = ppd.id_pegawai_potongan
		JOIN master_potongan mp
			ON mp.id = ppd.id_master_potongan
		WHERE pp.id_pegawai = ?
		AND pp.bulan = ?
		AND pp.tahun = ?
	", [
			$id_pegawai,
			$bulan,
			$tahun
		])->result_array();

		$total_potongan_lain = array_sum(
			array_column($potongan_detail, 'nominal')
		);

		/*
		|--------------------------------------------------------------------------
		| Bonus
		|--------------------------------------------------------------------------
		*/
		$bonus = $this->db
			->select_sum('nominal')
			->from('bonus')
			->where('id_pegawai', $id_pegawai)
			->where('bulan', $bulan)
			->where('tahun', $tahun)
			->get()
			->row_array();

		$total_bonus = (int) ($bonus['nominal'] ?? 0);

		/*
		|--------------------------------------------------------------------------
		| Pinjaman
		|--------------------------------------------------------------------------
		*/
		$query_pinjaman = $this->db->query("
	SELECT
		pd.id,
		pd.id_pinjaman,
		pd.nominal_tagihan,
		pd.nominal_bayar,
		pd.status_bayar,
		pj.sisa_pinjaman
	FROM pinjaman pj
	JOIN pinjaman_detail pd
		ON pj.id = pd.id_pinjaman
	WHERE pj.id_pegawai = ?
	AND pd.bulan = ?
	AND pd.tahun = ?
	LIMIT 1
", [
			$id_pegawai,
			$bulan,
			$tahun
		]);

		$pinjaman = $query_pinjaman->row_array();

		$id_detail_pinjaman = null;
		$id_pinjaman = null;
		$potongan_pinjaman = 0;
		$sisa_pinjaman = 0;

		if ($query_pinjaman->num_rows() > 0) {
			$id_detail_pinjaman = $pinjaman['id'];
			$id_pinjaman = $pinjaman['id_pinjaman'];

			/*
			 * Nominal cicilan terbaru diambil dari nominal_tagihan
			 * berdasarkan bulan dan tahun penggajian.
			 */
			$potongan_pinjaman = (int) ($pinjaman['nominal_tagihan'] ?? 0);

			/*
			 * Sisa pinjaman saat ini pada tabel master pinjaman.
			 */
			$sisa_pinjaman = (int) ($pinjaman['sisa_pinjaman'] ?? 0);
		}

		/*
		|--------------------------------------------------------------------------
		| Komponen pendapatan
		|--------------------------------------------------------------------------
		*/
		$gaji_pokok = (int) ($item['gaji_pokok'] ?? 0);
		$struktural = (int) ($item['struktural'] ?? 0);
		$tunjangan_pendidikan = (int) (
			$item['tunjangan_pendidikan'] ?? 0
		);
		$wali_kelas = (int) ($item['wali_kelas'] ?? 0);

		$total_pendapatan =
			$gaji_pokok +
			$struktural +
			$tunjangan_pendidikan +
			$wali_kelas;

		/*
		|--------------------------------------------------------------------------
		| Rumus potongan
		|--------------------------------------------------------------------------
		*/
		$rumus_potongan = $this->get_rumus_potongan();

		$persen_tidak_hadir = (float) $rumus_potongan['tidak_hadir'];
		$persen_uig_uik = (float) $rumus_potongan['uig_uik'];
		$persen_zakat = (float) $rumus_potongan['zakat'];

		// $potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_alfa;
		$potongan_tidak_hadir = ($total_pendapatan * $persen_tidak_hadir / 100) * $jumlah_hari_dipotong;

		$uig_uik = $total_pendapatan * $persen_uig_uik / 100;
		$zakat = $total_pendapatan * $persen_zakat / 100;

		$total_pengeluaran =
			$potongan_tidak_hadir +
			$uig_uik +
			$zakat +
			$total_potongan_lain +
			$potongan_pinjaman;

		$gaji_bersih = (
			$total_pendapatan -
			$total_pengeluaran
		) + $total_bonus;

		$data = [
			'id_pegawai' => $id_pegawai,
			'id_gaji' => $item['id_gaji'],
			'bulan' => $bulan,
			'tahun' => $tahun,
			'hari_efektif' => $hari_kerja,

			'jumlah_hadir' => $jumlah_hadir,
			'jumlah_tidak_hadir' => $jumlah_tidak_hadir,
			'jumlah_ijin' => $jumlah_ijin,
			'jumlah_ijin_pribadi' => $jumlah_ijin_pribadi,
			'jumlah_alfa' => $jumlah_alfa,

			'gaji_pokok' => $gaji_pokok,
			'struktural' => $struktural,
			'tunjangan_pendidikan' => $tunjangan_pendidikan,
			'wali_kelas' => $wali_kelas,
			'total_bonus' => $total_bonus,
			'total_pendapatan' => $total_pendapatan,

			'potongan_tidak_hadir' => $potongan_tidak_hadir,
			'uig_uik' => $uig_uik,
			'zakat' => $zakat,
			'total_potongan_lain' => $total_potongan_lain,
			'cicilan_pinjaman' => $potongan_pinjaman,
			'total_pengeluaran' => $total_pengeluaran,
			'gaji_bersih' => $gaji_bersih,

			'persen_potongan_tidak_hadir' => $persen_tidak_hadir,
			'persen_uig_uik' => $persen_uig_uik,
			'persen_zakat' => $persen_zakat,

			'tanggal_generates' => date('d-m-Y')
		];

		$this->db->trans_begin();

		if ($cek_penggajian) {
			$this->db
				->where('id', $cek_penggajian['id'])
				->update('penggajian', $data);

			$id_penggajian = $cek_penggajian['id'];

			$this->db
				->where('id_penggajian', $id_penggajian)
				->delete('penggajian_potongan');
		} else {
			$this->db->insert('penggajian', $data);
			$id_penggajian = $this->db->insert_id();
		}


		foreach ($potongan_detail as $potongan) {
			$this->db->insert('penggajian_potongan', [
				'id_penggajian' => $id_penggajian,
				'id_master_potongan' => $potongan['id_master_potongan'],
				'keterangan_potongan' => $potongan['keterangan_potongan'] ?? null,
				'nominal' => $potongan['nominal']
			]);
		}

		if ($id_detail_pinjaman !== null) {

			$cicilan_lama = 0;

			if ($cek_penggajian) {
				$cicilan_lama = (int) (
					$cek_penggajian['cicilan_pinjaman'] ?? 0
				);
			}


			$selisih_cicilan = $potongan_pinjaman - $cicilan_lama;

			$sisa_baru = $sisa_pinjaman - $selisih_cicilan;

			if ($sisa_baru < 0) {
				$sisa_baru = 0;
			}

			$this->db
				->where('id', $id_detail_pinjaman)
				->update('pinjaman_detail', [
					'nominal_bayar' => $potongan_pinjaman,
					'status_bayar' => 'Sudah',
					'tanggal_bayar' => date('d-m-Y')
				]);

			$data_master_pinjaman = [
				'sisa_pinjaman' => $sisa_baru,
				'status' => $sisa_baru == 0 ? 'Lunas' : 'Belum Lunas'
			];

			$this->db
				->where('id', $id_pinjaman)
				->update('pinjaman', $data_master_pinjaman);
		}

		if ($this->db->trans_status() === false) {
			$this->db->trans_rollback();
			return false;
		}

		$this->db->trans_commit();

		return true;
	}


	public function hari_efektif_result($bulan, $tahun)
	{
		if (empty($bulan) || empty($tahun)) {
			return [
				'status' => false,
				'data' => null
			];
		}

		$data = $this->db->get_where('hari_efektif', [
			'bulan' => $bulan,
			'tahun' => $tahun
		])->row_array();

		return [
			'status' => !empty($data),
			'data' => $data ?: null
		];
	}

	public function simpan_hari_efektif()
{
	$bulan = trim((string) $this->input->post('bulan'));
	$tahun = trim((string) $this->input->post('tahun'));
	$hari_efektif = (int) $this->input->post('hari_efektif');

	if ($bulan === '' || $tahun === '' || $hari_efektif <= 0) {
		return [
			'status' => false,
			'message' => 'Bulan, tahun, dan hari efektif wajib diisi'
		];
	}

	$cek = $this->db->get_where('hari_efektif', [
		'bulan' => $bulan,
		'tahun' => $tahun
	])->row_array();

	$hari_efektif_sebelumnya = $cek
		? (int) $cek['hari_efektif']
		: 0;

	$data = [
		'bulan' => $bulan,
		'tahun' => $tahun,
		'hari_efektif' => $hari_efektif
	];

	$admin = $this->session->userdata('admin');
	$id_pegawai_session = $admin['id_pegawai'] ?? null;

	if (empty($id_pegawai_session)) {
		return [
			'status' => false,
			'message' => 'Session pegawai tidak ditemukan'
		];
	}

	$this->db->trans_begin();

	if ($cek) {
		$this->db
			->where('id', $cek['id'])
			->update('hari_efektif', $data);
	} else {
		$this->db->insert('hari_efektif', $data);
	}

	$this->db->insert('riwayat_hari_efektif', [
		'id_pegawai' => (int) $id_pegawai_session,
		'hari_efektif_sebelumnya' => $hari_efektif_sebelumnya,
		'hari_efektif_diubah' => $hari_efektif,
		'bulan' => $bulan,
		'tahun'	=> $tahun,
		'tanggal' => date('d-m-Y'),
		'waktu' => date('H:i:s')
	]);

	if ($this->db->trans_status() === false) {
		$this->db->trans_rollback();

		return [
			'status' => false,
			'message' => 'Gagal menyimpan hari efektif'
		];
	}

	$this->db->trans_commit();

	return [
		'status' => true,
		'message' => 'Hari efektif berhasil disimpan'
	];
}

	private function get_hari_efektif($bulan, $tahun)
	{
		$data = $this->db->select('hari_efektif')->get_where('hari_efektif', [
			'bulan' => $bulan,
			'tahun' => $tahun
		])->row_array();

		if (!$data) {
			return null;
		}

		return (int) $data['hari_efektif'];
	}

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
<?php
class M_saldo extends CI_Model
{

	protected $id_user;
	protected $nama_user;
	function __construct()
	{
		parent::__construct();
		$this->id_user = $this->session->userdata('admin')['id_pegawai'];
		$this->nama_user = $this->session->userdata('admin')['nama_lengkap'];
	}


	// public function saldo_result()
	// {
	// 	$search = $this->input->post('search');
	// 	$tahun = $this->input->post('tahun');

	// 	$this->db->select("
    //     kb.id, kb.keterangan, COALESCE(sa.nominal,0) as saldo_awal,
    //     COALESCE((SELECT SUM(pm.jumlah) FROM pemasukan pm WHERE pm.simpan_ke = kb.id AND pm.tahun = '$tahun'),0) as total_masuk,
    //     COALESCE((SELECT SUM(pg.jumlah) FROM pengeluaran pg WHERE pg.sumber_dana = kb.id AND pg.tahun = '$tahun' ),0) as total_keluar,
    //     COALESCE((SELECT SUM(mk.nominal) FROM mutasi_kas mk WHERE mk.kas_masuk = kb.id AND mk.tahun='$tahun'),0) as mutasi_masuk,
    //     COALESCE((SELECT SUM(mk.nominal) FROM mutasi_kas mk WHERE mk.kas_keluar = kb.id AND mk.tahun='$tahun'),0) as mutasi_keluar,
    //     (
    //         COALESCE(sa.nominal,0) + COALESCE((SELECT SUM(pm.jumlah) FROM pemasukan pm WHERE pm.simpan_ke = kb.id AND pm.tahun='$tahun'),0)
    //         -
    //         COALESCE((SELECT SUM(pg.jumlah) FROM pengeluaran pg WHERE pg.sumber_dana = kb.id AND pg.tahun='$tahun'),0)
    //         +
    //         COALESCE((SELECT SUM(mk.nominal) FROM mutasi_kas mk WHERE mk.kas_masuk=kb.id AND mk.tahun='$tahun'),0)
    //         -
    //         COALESCE((SELECT SUM(mk.nominal) FROM mutasi_kas mk WHERE mk.kas_keluar=kb.id AND mk.tahun='$tahun'),0)
    //     ) as saldo_akhir
    // ");

	// 	$this->db->from('kasbank kb');
	// 	$this->db->join('saldo_awal sa', "sa.id_kasbank=kb.id AND sa.tahun='$tahun'", 'left');
	// 	if ($search != null) {
	// 		$this->db->like('kb.keterangan', $search);
	// 	}
	// 	return $this->db->get()->result_array();
	// }
	public function saldo_result()
{
    $search = $this->input->post('search');
    $tahun  = $this->input->post('tahun');

    $this->db->select("
        kb.id,
        kb.keterangan,
        (
            COALESCE(
                (SELECT SUM(sa.nominal)
                FROM saldo_awal sa
                WHERE sa.id_kasbank = kb.id
                AND sa.tahun < '$tahun'),0)
            +
            COALESCE(
                (SELECT SUM(pm.jumlah)
                FROM pemasukan pm
                WHERE pm.simpan_ke = kb.id
                AND pm.tahun < '$tahun'),0)
            -
            COALESCE(
                (SELECT SUM(pg.jumlah)
                FROM pengeluaran pg
                WHERE pg.sumber_dana = kb.id
                AND pg.tahun < '$tahun'),0)
            +
            COALESCE(
                (SELECT SUM(mk.nominal)
                FROM mutasi_kas mk
                WHERE mk.kas_masuk = kb.id
                AND mk.tahun < '$tahun'),0)
            -
            COALESCE(
                (SELECT SUM(mk.nominal)
                FROM mutasi_kas mk
                WHERE mk.kas_keluar = kb.id
                AND mk.tahun < '$tahun'),0)
            +
            COALESCE(
                (SELECT SUM(sa.nominal)
                FROM saldo_awal sa
                WHERE sa.id_kasbank = kb.id
                AND sa.tahun = '$tahun'),0)
        ) as saldo_awal,
        COALESCE(
            (SELECT SUM(pm.jumlah)
            FROM pemasukan pm
            WHERE pm.simpan_ke = kb.id
            AND pm.tahun = '$tahun'),0
        ) as total_masuk,
        COALESCE(
            (SELECT SUM(pg.jumlah)
            FROM pengeluaran pg
            WHERE pg.sumber_dana = kb.id
            AND pg.tahun = '$tahun'),0
        ) as total_keluar,
        COALESCE(
            (SELECT SUM(mk.nominal)
            FROM mutasi_kas mk
            WHERE mk.kas_masuk = kb.id
            AND mk.tahun = '$tahun'),0
        ) as mutasi_masuk,
        COALESCE(
            (SELECT SUM(mk.nominal)
            FROM mutasi_kas mk
            WHERE mk.kas_keluar = kb.id
            AND mk.tahun = '$tahun'),0
        ) as mutasi_keluar
    ");

    $this->db->from('kasbank kb');
    if ($search != null) {
        $this->db->like('kb.keterangan', $search);
    }
    $result = $this->db->get()->result_array();
    foreach ($result as &$row) {
        $row['saldo_akhir'] =$row['saldo_awal'] + $row['total_masuk'] - $row['total_keluar'] + $row['mutasi_masuk'] - $row['mutasi_keluar'];
    }

    return $result;
}
}
?>
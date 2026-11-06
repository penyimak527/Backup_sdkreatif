<?php defined('BASEPATH') OR exit('No direct script access allowed');
class M_pengaturan_cetak extends CI_Model
{
    private $jenis = array('Bukti Pembayaran', 'Surat Tunggakan');
    private $logo_storage = 'storage/spp/format_bukti/';
    private $logo_legacy = 'uploads/spp/format_bukti/';

    public function default_format($jenis)
    {
        $this->migrate_legacy_logos();
        return $this->db->where('jenis_dokumen', $jenis)->where('status', 'Aktif')->where('status_default', 'Ya')->order_by('id', 'DESC')->limit(1)->get('spp_pengaturan_cetak')->row_array();
    }

    public function listing()
    {
        $this->migrate_legacy_logos();
        return $this->db->where_in('jenis_dokumen', $this->jenis)->order_by('jenis_dokumen', 'ASC')->order_by("status_default='Ya'", 'DESC', false)->order_by('id', 'DESC')->get('spp_pengaturan_cetak')->result_array();
    }

    public function detail($id)
    {
        $this->migrate_legacy_logos();
        $r = $this->db->where('id', (int) $id)->where_in('jenis_dokumen', $this->jenis)->get('spp_pengaturan_cetak')->row_array();
        if (!$r)
            return array('result' => 'false', 'message' => 'Format tidak ditemukan.');
        $r['pengaturan'] = $this->decode($r['pengaturan_json'] ?? '');
        return array('result' => 'true', 'data' => $r);
    }

    public function save($p, $actor)
    {
        $this->migrate_legacy_logos();
        $id = (int) ($p['id'] ?? 0);
        $jenis = trim((string) ($p['jenis_dokumen'] ?? ''));
        if (!in_array($jenis, $this->jenis, true))
            return array('result' => 'false', 'message' => 'Jenis format tidak valid.');

        $nama = trim((string) ($p['nama_format'] ?? ''));
        $judul = trim((string) ($p['judul'] ?? ''));
        if ($nama === '' || $judul === '')
            return array('result' => 'false', 'message' => 'Nama format dan judul wajib diisi.');

        $ukuran = trim((string) ($p['ukuran_kertas'] ?? '80mm'));
        if (!in_array($ukuran, array('58mm', '80mm', 'A5', 'A4', 'Custom'), true))
            return array('result' => 'false', 'message' => 'Ukuran kertas tidak valid.');
        $orientasi = trim((string) ($p['orientasi'] ?? 'Portrait'));
        if (!in_array($orientasi, array('Portrait', 'Landscape'), true))
            $orientasi = 'Portrait';
        $status = (($p['status'] ?? 'Aktif') === 'Nonaktif') ? 'Nonaktif' : 'Aktif';
        $default = (($p['status_default'] ?? 'Tidak') === 'Ya') ? 'Ya' : 'Tidak';
        if ($default === 'Ya')
            $status = 'Aktif';

        $before = $id ? $this->db->where('id', $id)->where_in('jenis_dokumen', $this->jenis)->get('spp_pengaturan_cetak')->row_array() : null;
        if ($id && !$before)
            return array('result' => 'false', 'message' => 'Format tidak ditemukan.');
        $oldCfg = $this->decode($before['pengaturan_json'] ?? '');
        $logo = (string) ($oldCfg['logo_sekolah'] ?? '');

        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, array('png', 'jpg', 'jpeg', 'webp'), true))
                return array('result' => 'false', 'message' => 'Logo hanya boleh PNG, JPG, JPEG, atau WEBP.');
            if ((int) ($_FILES['logo']['size'] ?? 0) > 2 * 1024 * 1024)
                return array('result' => 'false', 'message' => 'Ukuran logo maksimal 2 MB.');
            $dir = $this->logo_directory();
            if ($dir === '')
                return array('result' => 'false', 'message' => 'Folder storage logo tidak dapat dibuat.');
            $filename = 'logo_' . date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $ext;
            if (!@move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $filename))
                return array('result' => 'false', 'message' => 'Upload logo gagal.');
            $logo = $this->logo_storage . $filename;
        }

        $cfg = array(
            'nama_sekolah' => trim((string) ($p['nama_sekolah'] ?? '')),
            'alamat_sekolah' => trim((string) ($p['alamat_sekolah'] ?? '')),
            'telepon_sekolah' => trim((string) ($p['telepon_sekolah'] ?? '')),
            'logo_sekolah' => $logo,
            'tampilkan_logo' => (($p['tampilkan_logo'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya'),
            'margin_atas' => $this->decimal($p['margin_atas'] ?? 5, 5),
            'margin_bawah' => $this->decimal($p['margin_bawah'] ?? 5, 5),
            'margin_kiri' => $this->decimal($p['margin_kiri'] ?? 5, 5),
            'margin_kanan' => $this->decimal($p['margin_kanan'] ?? 5, 5),
            'tampilkan_terbilang' => (($p['tampilkan_terbilang'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya'),
            'tampilkan_uang_diterima' => (($p['tampilkan_uang_diterima'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya'),
            'tampilkan_kembalian' => (($p['tampilkan_kembalian'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya'),
            'tampilkan_sisa_tagihan' => (($p['tampilkan_sisa_tagihan'] ?? 'Ya') === 'Tidak' ? 'Tidak' : 'Ya'),
            'posisi_tanda_tangan' => in_array(($p['posisi_tanda_tangan'] ?? 'Kanan'), array('Kiri', 'Tengah', 'Kanan'), true) ? $p['posisi_tanda_tangan'] : 'Kanan'
        );

        $data = array(
            'jenis_dokumen' => $jenis,
            'nama_format' => mb_substr($nama, 0, 120),
            'ukuran_kertas' => $ukuran,
            'orientasi' => $orientasi,
            'judul' => mb_substr($judul, 0, 200),
            'header_text' => trim((string) ($p['header_text'] ?? '')),
            'footer_text' => trim((string) ($p['footer_text'] ?? '')),
            'nama_penandatangan' => trim((string) ($p['nama_penandatangan'] ?? '')),
            'jabatan_penandatangan' => trim((string) ($p['jabatan_penandatangan'] ?? '')),
            'pengaturan_json' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
            'status' => $status,
            'status_default' => $default,
            'updated_by' => (int) $actor,
            'updated_at' => date('Y-m-d H:i:s'),
            'lebar_kertas' => $ukuran === 'Custom' ? $this->decimal($p['lebar_kertas'] ?? 0, 0) : null,
            'tinggi_kertas' => $ukuran === 'Custom' ? $this->decimal($p['tinggi_kertas'] ?? 0, 0) : null
        );
        if ($ukuran === 'Custom' && ((float) $data['lebar_kertas'] <= 0 || (float) $data['tinggi_kertas'] <= 0))
            return array('result' => 'false', 'message' => 'Lebar dan tinggi kertas Custom wajib diisi.');

        $this->db->trans_begin();
        if ($default === 'Ya')
            $this->db->where('jenis_dokumen', $jenis)->update('spp_pengaturan_cetak', array('status_default' => 'Tidak'));
        if ($id)
            $this->db->where('id', $id)->update('spp_pengaturan_cetak', $data);
        else {
            $this->db->insert('spp_pengaturan_cetak', $data);
            $id = (int) $this->db->insert_id();
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Gagal menyimpan format bukti.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Format bukti berhasil disimpan.', 'id' => $id);
    }

    public function set_default($id, $actor)
    {
        $this->migrate_legacy_logos();
        $r = $this->db->where('id', (int) $id)->where_in('jenis_dokumen', $this->jenis)->get('spp_pengaturan_cetak')->row_array();
        if (!$r)
            return array('result' => 'false', 'message' => 'Format tidak ditemukan.');
        $this->db->trans_begin();
        $this->db->where('jenis_dokumen', $r['jenis_dokumen'])->update('spp_pengaturan_cetak', array('status_default' => 'Tidak'));
        $this->db->where('id', (int) $id)->update('spp_pengaturan_cetak', array('status_default' => 'Ya', 'status' => 'Aktif', 'updated_by' => (int) $actor, 'updated_at' => date('Y-m-d H:i:s')));
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('result' => 'false', 'message' => 'Gagal menetapkan format default.');
        }
        $this->db->trans_commit();
        return array('result' => 'true', 'message' => 'Format berhasil dijadikan default.');
    }

    private function logo_directory()
    {
        $dir = FCPATH . $this->logo_storage;
        if (!is_dir($dir) && !@mkdir($dir, 0755, true))
            return '';
        return $dir;
    }

    private function migrate_legacy_logos()
    {
        if (!$this->db->table_exists('spp_pengaturan_cetak'))
            return;

        $rows = $this->db
            ->select('id, pengaturan_json')
            ->where_in('jenis_dokumen', $this->jenis)
            ->like('pengaturan_json', $this->logo_legacy)
            ->get('spp_pengaturan_cetak')
            ->result_array();

        if (!$rows)
            return;

        $targetDir = $this->logo_directory();
        if ($targetDir === '')
            return;

        foreach ($rows as $row) {
            $cfg = $this->decode($row['pengaturan_json'] ?? '');
            $legacy = trim((string) ($cfg['logo_sekolah'] ?? ''));
            if ($legacy === '' || strpos($legacy, $this->logo_legacy) !== 0)
                continue;

            $source = FCPATH . ltrim($legacy, '/');
            if (!is_file($source))
                continue;

            $filename = 'legacy_' . substr(md5($legacy), 0, 10) . '_' . basename($legacy);
            $target = $targetDir . $filename;
            if (!is_file($target) && !@copy($source, $target))
                continue;

            $cfg['logo_sekolah'] = $this->logo_storage . $filename;
            $this->db->where('id', (int) $row['id'])->update('spp_pengaturan_cetak', array(
                'pengaturan_json' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }
    }

    private function decode($json)
    {
        $d = json_decode((string) $json, true);
        return is_array($d) ? $d : array();
    }
    private function decimal($value, $default = 0)
    {
        return is_numeric($value) ? (float) $value : (float) $default;
    }
}

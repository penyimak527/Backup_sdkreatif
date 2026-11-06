<?php defined('BASEPATH') OR exit('No direct script access allowed');
class M_pengaturan_whatsapp extends CI_Model {
    private $types=['Bukti Pembayaran','Surat Tunggakan','Setoran Tabungan','Penarikan Tabungan'];
    public function listing(){return $this->db->order_by('jenis_template')->order_by("status_default='Ya'",'DESC',FALSE)->order_by('id','DESC')->get('spp_template_whatsapp')->result_array();}
    public function detail($id){$r=$this->db->where('id',$id)->get('spp_template_whatsapp')->row_array();return $r?['result'=>'true','data'=>$r]:['result'=>'false','message'=>'Template WhatsApp tidak ditemukan.'];}
    public function save($p,$actor){
        $id=(int)($p['id']??0);$jenis=trim((string)($p['jenis_template']??''));$nama=trim((string)($p['nama_template']??''));$isi=trim((string)($p['isi_template']??''));
        if(!in_array($jenis,$this->types,true))return ['result'=>'false','message'=>'Jenis template tidak valid.'];
        if($nama===''||$isi==='')return ['result'=>'false','message'=>'Nama template dan isi pesan wajib diisi.'];
        if(mb_strlen($isi)>4000)return ['result'=>'false','message'=>'Isi pesan terlalu panjang.'];
        $invalid=$this->invalid_variables($jenis,$isi);if($invalid)return ['result'=>'false','message'=>'Variabel tidak dikenal: '.implode(', ',$invalid).'. Gunakan variabel yang tersedia di halaman.'];
        $status=(($p['status']??'Aktif')==='Nonaktif')?'Nonaktif':'Aktif';$def=(($p['status_default']??'Tidak')==='Ya')?'Ya':'Tidak';if($def==='Ya')$status='Aktif';
        $data=['jenis_template'=>$jenis,'nama_template'=>mb_substr($nama,0,150),'isi_template'=>$isi,'status'=>$status,'status_default'=>$def,'updated_by'=>$actor,'updated_at'=>date('Y-m-d H:i:s')];
        if($id&&!$this->db->where('id',$id)->count_all_results('spp_template_whatsapp'))return ['result'=>'false','message'=>'Template tidak ditemukan.'];
        $this->db->trans_begin();if($def==='Ya')$this->db->where('jenis_template',$jenis)->update('spp_template_whatsapp',['status_default'=>'Tidak']);if($id)$this->db->where('id',$id)->update('spp_template_whatsapp',$data);else{$this->db->insert('spp_template_whatsapp',$data);$id=(int)$this->db->insert_id();}
        if($this->db->trans_status()===FALSE){$this->db->trans_rollback();return ['result'=>'false','message'=>'Gagal menyimpan template WhatsApp.'];}$this->db->trans_commit();return ['result'=>'true','message'=>'Template WhatsApp berhasil disimpan.','id'=>$id];
    }
    public function set_default($id,$actor){$r=$this->db->where('id',$id)->get('spp_template_whatsapp')->row_array();if(!$r)return ['result'=>'false','message'=>'Template tidak ditemukan.'];$this->db->trans_begin();$this->db->where('jenis_template',$r['jenis_template'])->update('spp_template_whatsapp',['status_default'=>'Tidak']);$this->db->where('id',$id)->update('spp_template_whatsapp',['status'=>'Aktif','status_default'=>'Ya','updated_by'=>$actor,'updated_at'=>date('Y-m-d H:i:s')]);if($this->db->trans_status()===FALSE){$this->db->trans_rollback();return ['result'=>'false','message'=>'Gagal menetapkan template default.'];}$this->db->trans_commit();return ['result'=>'true','message'=>'Template default berhasil diperbarui.'];}
    public function allowed_variables($jenis){
        $common=['{nama_wali}','{nama_siswa}','{kelas}','{tanggal}','{nama_sekolah}'];
        if($jenis==='Bukti Pembayaran')return array_merge($common,['{no_transaksi}','{total_bayar}','{metode_pembayaran}','{nama_petugas}']);
        if($jenis==='Surat Tunggakan')return array_merge($common,['{no_surat}','{total_tunggakan}','{jumlah_tagihan}']);
        return array_merge($common,['{no_transaksi}','{nominal}','{saldo}','{metode_pembayaran}','{nama_petugas}']);
    }
    private function invalid_variables($jenis,$isi){preg_match_all('/\{[a-zA-Z0-9_]+\}/',$isi,$m);$allowed=$this->allowed_variables($jenis);return array_values(array_diff(array_unique($m[0]??[]),$allowed));}
}

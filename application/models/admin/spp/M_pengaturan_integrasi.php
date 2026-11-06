<?php defined('BASEPATH') OR exit('No direct script access allowed');
class M_pengaturan_integrasi extends CI_Model {
 public function listing(){ return $this->db->query("SELECT j.id,j.nama_jenis,j.tipe_default,COALESCE(i.integrasi_pemasukan,'Tidak') integrasi_pemasukan,i.id_kode_akun,i.id_kasbank,ka.keterangan AS nama_kasbank,ak.keterangan AS nama_akun FROM tagihan_jenis j LEFT JOIN spp_pengaturan_integrasi i ON i.id_jenis_tagihan=j.id LEFT JOIN kasbank ka ON ka.id=i.id_kasbank LEFT JOIN kode_akun ak ON ak.id=i.id_kode_akun ORDER BY j.nama_jenis")->result_array(); }
 public function options(){ return ['akun'=>$this->db->where('jenis','Pemasukan')->order_by('keterangan')->get('kode_akun')->result_array(),'kasbank'=>$this->db->order_by('keterangan')->get('kasbank')->result_array()]; }
 public function save($jenis,$integrasi,$akun,$kas,$actor){
  if($jenis<1 || !in_array($integrasi,['Ya','Tidak'],true)) return ['result'=>'false','message'=>'Jenis tagihan dan pilihan integrasi wajib valid.'];
  if(!$this->db->where('id',$jenis)->count_all_results('tagihan_jenis')) return ['result'=>'false','message'=>'Jenis tagihan tidak ditemukan.'];
  if($integrasi==='Ya' && ($akun<=0 || !$this->db->where('id',$akun)->where('jenis','Pemasukan')->count_all_results('kode_akun'))) return ['result'=>'false','message'=>'Pos penerimaan wajib dipilih dari Kode Akun.'];
  if($integrasi==='Ya' && $kas<=0) return ['result'=>'false','message'=>'Kas/Bank wajib dipilih ketika integrasi Pemasukan aktif.'];
  if($kas>0 && !$this->db->where('id',$kas)->count_all_results('kasbank')) return ['result'=>'false','message'=>'Kas/bank tidak ditemukan.'];
  if($integrasi==='Tidak'){ $akun=0; $kas=0; }
  $data=['integrasi_pemasukan'=>$integrasi,'id_kode_akun'=>$akun?:NULL,'id_kasbank'=>$kas?:NULL,'updated_by'=>$actor,'updated_at'=>date('Y-m-d H:i:s')];
  $this->db->trans_begin(); $prev=$this->db->get_where('spp_pengaturan_integrasi',['id_jenis_tagihan'=>$jenis])->row_array();
  if($prev) $this->db->where('id',(int)$prev['id'])->update('spp_pengaturan_integrasi',$data);
  else $this->db->insert('spp_pengaturan_integrasi',array_merge(['id_jenis_tagihan'=>$jenis],$data));
  if($this->db->trans_status()===FALSE){$this->db->trans_rollback();return ['result'=>'false','message'=>'Pengaturan gagal disimpan.'];}
  $this->db->trans_commit();return ['result'=>'true','message'=>'Integrasi jenis tagihan berhasil disimpan.'];
 }
}

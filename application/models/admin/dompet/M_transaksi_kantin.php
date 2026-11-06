<?php defined('BASEPATH') or exit('No direct script access allowed');
class M_transaksi_kantin extends CI_Model
{
    public function result($f){$this->apply($f);$total=(int)$this->db->count_all_results('transaksi_kantin');$this->apply($f);$rows=$this->db->order_by('id','DESC')->limit($f['limit'],($f['page']-1)*$f['limit'])->get('transaksi_kantin')->result_array();return ['rows'=>$rows,'meta'=>['page'=>$f['page'],'limit'=>$f['limit'],'total'=>$total,'pages'=>max(1,(int)ceil($total/$f['limit']))]];}
    private function apply($f){if($f['search']!=='')$this->db->group_start()->like('no_transaksi',$f['search'])->or_like('nis',$f['search'])->or_like('nama_siswa',$f['search'])->group_end();if($f['status']!=='')$this->db->where('status_transaksi',$f['status']);if($f['tanggal_awal']!=='')$this->db->where('tanggal >=',$f['tanggal_awal']);if($f['tanggal_akhir']!=='')$this->db->where('tanggal <=',$f['tanggal_akhir']);}
    public function detail($id){return $this->db->get_where('transaksi_kantin',['id'=>$id])->row_array();}
}

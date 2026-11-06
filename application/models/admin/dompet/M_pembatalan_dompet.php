<?php defined('BASEPATH') or exit('No direct script access allowed');
class M_pembatalan_dompet extends CI_Model
{
    public function filter_options(){return ['jenis'=>$this->db->select('jenis_transaksi value')->where('jenis_transaksi IS NOT NULL',null,false)->where('jenis_transaksi !=','')->group_by('jenis_transaksi')->order_by('jenis_transaksi')->get('pembatalan_dompet')->result_array(),'status'=>$this->db->select('status_pembatalan value')->where('status_pembatalan IS NOT NULL',null,false)->where('status_pembatalan !=','')->group_by('status_pembatalan')->order_by('status_pembatalan')->get('pembatalan_dompet')->result_array()];}
    public function result($f){if($f['search']!=='')$this->db->group_start()->like('no_pembatalan',$f['search'])->or_like('no_transaksi',$f['search'])->group_end();if($f['status']!=='')$this->db->where('status_pembatalan',$f['status']);if($f['jenis']!=='')$this->db->where('jenis_transaksi',$f['jenis']);$total=(int)$this->db->count_all_results('pembatalan_dompet',false);$rows=$this->db->order_by('id','DESC')->limit($f['limit'],($f['page']-1)*$f['limit'])->get()->result_array();return ['rows'=>$rows,'meta'=>['page'=>$f['page'],'limit'=>$f['limit'],'total'=>$total,'pages'=>max(1,(int)ceil($total/$f['limit']))]];}
    public function detail($id){return $this->db->get_where('pembatalan_dompet',['id'=>$id])->row_array();}
}

<?php defined('BASEPATH') or exit('No direct script access allowed');
class M_pengumuman extends CI_Model
{
    public function result($f){if($f['search']!=='')$this->db->group_start()->like('judul',$f['search'])->or_like('isi',$f['search'])->group_end();if($f['status']!=='')$this->db->where('status_aktif',$f['status']);$total=(int)$this->db->count_all_results('pengumuman_dompet',false);$rows=$this->db->order_by('id','DESC')->limit($f['limit'],($f['page']-1)*$f['limit'])->get()->result_array();return ['rows'=>$rows,'meta'=>['page'=>$f['page'],'limit'=>$f['limit'],'total'=>$total,'pages'=>max(1,(int)ceil($total/$f['limit']))]];}
    public function detail($id){return $this->db->get_where('pengumuman_dompet',['id'=>$id])->row_array();}
    public function save($id,$p,$u){if($id>0&&!$this->db->where('id',$id)->count_all_results('pengumuman_dompet'))return $this->err('Pengumuman tidak ditemukan.',404);$data=array_merge($p,['tanggal'=>date('Y-m-d'),'waktu'=>date('H:i:s'),'id_user'=>(int)$u['id'],'nama_user'=>$u['nama_user']]);$ok=$id?$this->db->where('id',$id)->update('pengumuman_dompet',$data):$this->db->insert('pengumuman_dompet',array_merge($data,['status_aktif'=>'AKTIF']));return $ok?['success'=>true,'data'=>['id'=>$id?:$this->db->insert_id()]]:$this->err('Pengumuman gagal disimpan.',500);}
    public function status($id,$status){return $this->db->where('id',$id)->update('pengumuman_dompet',['status_aktif'=>$status])?['success'=>true]:$this->err('Status gagal diperbarui.',500);}
    private function err($m,$s){return ['success'=>false,'message'=>$m,'status'=>$s];}
}

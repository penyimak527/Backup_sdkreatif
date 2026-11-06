<?php defined('BASEPATH') or exit('No direct script access allowed');
class M_pengaturan_dompet extends CI_Model
{
    public function get(){return $this->db->order_by('id','DESC')->limit(1)->get('pengaturan_dompet')->row_array();}
    public function save($p,$u){$row=$this->get();$data=array_merge($p,['tanggal_update'=>date('Y-m-d'),'waktu_update'=>date('H:i:s'),'id_user_update'=>(int)$u['id'],'nama_user_update'=>$u['nama_user']]);$ok=$row?$this->db->where('id',$row['id'])->update('pengaturan_dompet',$data):$this->db->insert('pengaturan_dompet',$data);return $ok?['success'=>true]:['success'=>false,'message'=>'Pengaturan gagal disimpan.','status'=>500];}
}

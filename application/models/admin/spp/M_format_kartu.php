<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_format_kartu extends CI_Model
{
    public function listing()
    {
        $rows=$this->db->where('jenis_dokumen','Kartu SPP')->order_by("status_default='Ya'",'DESC',FALSE)->order_by('id','DESC')->get('spp_pengaturan_cetak')->result_array();
        foreach($rows as &$row)$row=$this->normalize($row); unset($row); return $rows;
    }
    public function detail($id)
    {
        $row=$this->db->where('id',$id)->where('jenis_dokumen','Kartu SPP')->get('spp_pengaturan_cetak')->row_array();
        return $row?$this->normalize($row):array();
    }
    private function normalize($row)
    {
        $cfg=array(); if(!empty($row['pengaturan_json'])){$tmp=json_decode($row['pengaturan_json'],TRUE);if(is_array($tmp))$cfg=$tmp;}
        if(!is_numeric($row['lebar_kertas']??NULL))$row['lebar_kertas']=(float)($cfg['lebar']??210);
        if(!is_numeric($row['tinggi_kertas']??NULL))$row['tinggi_kertas']=(float)($cfg['tinggi']??148);
        return $row;
    }
    public function save()
    {
        $id=(int)$this->input->post('id');
        $name=trim((string)$this->input->post('nama_format',TRUE));
        $default=$this->input->post('status_default')==='Ya'?'Ya':'Tidak';
        $status=$this->input->post('status')==='Nonaktif'?'Nonaktif':'Aktif';
        $json=(string)$this->input->post('pengaturan_json',FALSE);
        $settings=json_decode($json,TRUE);
        if ($name==='') return array('result'=>'false','message'=>'Nama format wajib diisi.');
        if (!is_array($settings)) return array('result'=>'false','message'=>'Pengaturan posisi cetak tidak valid.');
        $allowed=array('Tanggal','Jenis/Bulan','Nominal','Petugas');
        $cols=array_values(array_intersect($allowed,(array)($settings['kolom']??array())));
        $max=array('jumlah_baris'=>50,'jarak_baris'=>100,'posisi_x'=>500,'posisi_y'=>500,'lebar_tanggal'=>500,'lebar_jenis'=>500,'lebar_nominal'=>500,'lebar_petugas'=>500);
        $cfg=array();
        foreach ($max as $field=>$cap) {
            $n=$settings[$field]??NULL;
            if (!is_numeric($n) || (float)$n<0 || (float)$n>$cap) return array('result'=>'false','message'=>'Nilai '.$field.' tidak valid.');
            $cfg[$field]=$field==='jumlah_baris'?(int)$n:(float)$n;
        }
        if ($cfg['jumlah_baris']<1 || !count($cols))return array('result'=>'false','message'=>'Jumlah baris dan kolom cetak harus diisi.');
        $cfg['kolom']=$cols;
        $width=(float)$this->input->post('lebar_kertas'); $height=(float)$this->input->post('tinggi_kertas');
        if ($width<50 || $width>500 || $height<50 || $height>500) return array('result'=>'false','message'=>'Ukuran kartu harus 50–500 mm.');
        if ($default==='Ya') $status='Aktif';
        $data=array('jenis_dokumen'=>'Kartu SPP','nama_format'=>mb_substr($name,0,120),
            'judul'=>'Kartu SPP','ukuran_kertas'=>'A5','orientasi'=>'Landscape',
            'lebar_kertas'=>$width,'tinggi_kertas'=>$height,
            'pengaturan_json'=>json_encode($cfg,JSON_UNESCAPED_UNICODE),
            'status'=>$status,'status_default'=>$default,'updated_at'=>date('Y-m-d H:i:s'));
        $admin=$this->session->userdata('admin'); $data['updated_by']=(int)($admin['id_user']??0);
        if ($id>0 && !$this->detail($id)) return array('result'=>'false','message'=>'Format kartu tidak ditemukan.');
        $this->db->trans_begin();
        if ($default==='Ya') $this->db->where('jenis_dokumen','Kartu SPP')->update('spp_pengaturan_cetak',array('status_default'=>'Tidak'));
        if ($id>0) $this->db->where('id',$id)->update('spp_pengaturan_cetak',$data);
        else $this->db->insert('spp_pengaturan_cetak',$data);
        if ($this->db->trans_status()===FALSE){$this->db->trans_rollback();return array('result'=>'false','message'=>'Gagal menyimpan format.');}
        $this->db->trans_commit();
        return array('result'=>'true','message'=>'Format kartu berhasil disimpan.');
    }
    public function set_default($id)
    {
        $row=$this->detail($id); if (!$row) return array('result'=>'false','message'=>'Format tidak ditemukan.');
        $this->db->trans_begin();
        $this->db->where('jenis_dokumen','Kartu SPP')->update('spp_pengaturan_cetak',array('status_default'=>'Tidak'));
        $this->db->where('id',$id)->update('spp_pengaturan_cetak',array('status_default'=>'Ya','status'=>'Aktif'));
        if ($this->db->trans_status()===FALSE){$this->db->trans_rollback();return array('result'=>'false','message'=>'Gagal menetapkan default.');}
        $this->db->trans_commit();return array('result'=>'true','message'=>'Format default diperbarui.');
    }
}

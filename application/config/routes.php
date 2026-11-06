<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Login';
$route['wali/dompet']['get'] = 'wali/dompet/index';
$route['wali/dompet/history-belanja']['get'] = 'wali/dompet/history_belanja';
$route['wali/dompet/history-isi-saldo']['get'] = 'wali/dompet/history_isi_saldo';
$route['wali/dompet/setting-limit']['get'] = 'wali/dompet/setting_limit';
$route['wali/dompet/buat-pengajuan']['post'] = 'wali/dompet/buat_pengajuan';
$route['wali/dompet/simpan-limit']['post'] = 'wali/dompet/simpan_limit';
$route['api/v1/auth/login']['post'] = 'api/dompet/auth/login';
$route['api/v1/auth/sesi']['get'] = 'api/dompet/auth/sesi';
$route['api/v1/auth/logout']['post'] = 'api/dompet/auth/logout';
$route['api/v1/auth/ubah-password']['post'] = 'api/dompet/auth/ubah_password';
$route['api/v1/internal/auth/login']['post'] = 'api/dompet/auth/login_internal';
$route['api/v1/internal/auth/sesi']['get'] = 'api/dompet/auth/sesi_internal';
$route['api/v1/internal/auth/logout']['post'] = 'api/dompet/auth/logout_internal';
$route['api/v1/internal/dompet/pengajuan/(:any)/setujui']['post'] = 'api/dompet/verifikasi_saldo/setujui/$1';
$route['api/v1/internal/dompet/pengajuan/(:any)/tolak']['post'] = 'api/dompet/verifikasi_saldo/tolak/$1';
$route['api/v1/internal/dompet/pengajuan/(:any)']['get'] = 'api/dompet/verifikasi_saldo/detail/$1';
$route['api/v1/internal/dompet/pengajuan']['get'] = 'api/dompet/verifikasi_saldo/index';
$route['api/v1/petugas/transaksi-kantin/(:any)/ajukan-pembatalan']['post'] = 'api/dompet/pembatalan_dompet/ajukan_kantin/$1';
$route['api/v1/internal/dompet/transaksi-isi-saldo/(:any)/ajukan-pembatalan']['post'] = 'api/dompet/pembatalan_dompet/ajukan_isi_saldo/$1';
$route['api/v1/internal/dompet/pembatalan/(:any)/setujui']['post'] = 'api/dompet/pembatalan_dompet/setujui/$1';
$route['api/v1/internal/dompet/pembatalan/(:any)/tolak']['post'] = 'api/dompet/pembatalan_dompet/tolak/$1';
$route['api/v1/internal/dompet/pembatalan/(:any)']['get'] = 'api/dompet/pembatalan_dompet/detail/$1';
$route['api/v1/internal/dompet/pembatalan']['get'] = 'api/dompet/pembatalan_dompet/index';
$route['api/v1/profil']['get'] = 'api/dompet/profil/index';
$route['api/v1/wali/anak']['get'] = 'api/dompet/wali/anak';
$route['api/v1/wali/anak/(:num)/ringkasan']['get'] = 'api/dompet/wali/ringkasan/$1';
$route['api/v1/wali/anak/(:num)/dompet']['get'] = 'api/dompet/dompet/detail/$1';
$route['api/v1/wali/anak/(:num)/penggunaan-harian']['get'] = 'api/dompet/dompet/penggunaan_harian/$1';
$route['api/v1/wali/anak/(:num)/ubah-limit']['post'] = 'api/dompet/dompet/ubah_limit/$1';
$route['api/v1/wali/anak/(:num)/histori-limit']['get'] = 'api/dompet/dompet/histori_limit/$1';
$route['api/v1/wali/anak/(:num)/riwayat-transaksi/(:any)/(:any)']['get'] = 'api/dompet/dompet/detail_transaksi/$1/$2/$3';
$route['api/v1/wali/anak/(:num)/riwayat-transaksi']['get'] = 'api/dompet/dompet/riwayat_transaksi/$1';
$route['api/v1/pengumuman']['get'] = 'api/dompet/pengumuman/index';
$route['api/v1/pengumuman/(:num)']['get'] = 'api/dompet/pengumuman/detail/$1';
$route['api/v1/dompet/pengaturan-isi-saldo']['get'] = 'api/dompet/isi_saldo/pengaturan';
$route['api/v1/wali/anak/(:num)/pengajuan-isi-saldo']['post'] = 'api/dompet/isi_saldo/buat/$1';
$route['api/v1/wali/pengajuan-isi-saldo/(:any)/batalkan']['post'] = 'api/dompet/isi_saldo/batalkan/$1';
$route['api/v1/wali/pengajuan-isi-saldo/(:any)']['get'] = 'api/dompet/isi_saldo/detail/$1';
$route['api/v1/wali/pengajuan-isi-saldo']['get'] = 'api/dompet/isi_saldo/index';
$route['api/v1/petugas/dashboard']['get'] = 'api/dompet/petugas/dashboard';
$route['api/v1/petugas/scan-qr']['post'] = 'api/dompet/petugas/scan_qr';
$route['api/v1/petugas/cari-siswa']['get'] = 'api/dompet/petugas/cari_siswa';
$route['api/v1/petugas/transaksi-kantin/permintaan/(:any)']['get'] = 'api/dompet/transaksi_kantin/permintaan/$1';
$route['api/v1/petugas/transaksi-kantin/(:any)']['get'] = 'api/dompet/transaksi_kantin/detail/$1';
$route['api/v1/petugas/transaksi-kantin']['get'] = 'api/dompet/transaksi_kantin/index';
$route['api/v1/petugas/transaksi-kantin']['post'] = 'api/dompet/transaksi_kantin/index';
$route['translate_uri_dashes'] = FALSE;

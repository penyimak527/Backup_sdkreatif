<?php
defined('BASEPATH') or exit('No direct script access allowed');

$masa_berlaku_token = getenv('API_TOKEN_LIFETIME_SECONDS');
$panjang_minimal_password = getenv('API_PASSWORD_MIN_LENGTH');
$jumlah_data_per_halaman = getenv('API_DEFAULT_PAGE_SIZE');
$jumlah_data_maksimal = getenv('API_MAX_PAGE_SIZE');
$ukuran_maksimal_bukti = getenv('API_UPLOAD_PROOF_MAX_KB');
$level_internal = getenv('API_INTERNAL_LEVELS');
$integrasi_keuangan = getenv('API_FINANCE_INTEGRATION_ENABLED');
$akun_titipan = getenv('API_FINANCE_PRINCIPAL_ACCOUNT_ID');
$akun_biaya = getenv('API_FINANCE_FEE_ACCOUNT_ID');

$config['api_token_lifetime_seconds'] = $masa_berlaku_token !== false
    ? (int) $masa_berlaku_token
    : 2592000;

$config['api_password_min_length'] = $panjang_minimal_password !== false
    ? (int) $panjang_minimal_password
    : 6;

$config['api_default_page_size'] = $jumlah_data_per_halaman !== false
    ? (int) $jumlah_data_per_halaman
    : 20;

$config['api_max_page_size'] = $jumlah_data_maksimal !== false
    ? (int) $jumlah_data_maksimal
    : 100;

$config['api_upload_proof_max_kb'] = $ukuran_maksimal_bukti !== false
    ? (int) $ukuran_maksimal_bukti
    : 5120;

$config['api_internal_levels'] = $level_internal !== false
    ? array_values(array_filter(array_map('trim', explode(',', $level_internal))))
    : array('Bendahara');

$config['api_finance_integration_enabled'] = $integrasi_keuangan !== false
    && in_array(strtolower(trim($integrasi_keuangan)), array('1', 'true', 'yes', 'on'), true);

$config['api_finance_principal_account_id'] = $akun_titipan !== false
    ? (int) $akun_titipan
    : 0;

$config['api_finance_fee_account_id'] = $akun_biaya !== false
    ? (int) $akun_biaya
    : 0;

$config['api_role_modes'] = array(
    'Wali Murid' => 'WALI',
    'Admin' => 'PETUGAS',
    'Dompet Admin' => 'PETUGAS',
    'Bendahara' => 'PETUGAS',
);

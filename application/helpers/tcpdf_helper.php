<?php
function tcpdf()
{
	require_once APPPATH . 'third_party/tcpdf/tcpdf.php';
	return new TCPDF();
}

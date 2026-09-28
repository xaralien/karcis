<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Tidak ada rute kustom. Semua URL memakai pola bawaan CodeIgniter:
|   index.php/<controller>/<method>/<parameter>
| Contoh: /event/detail/senandung-senja-2026 , /admin/orders/detail/KRC260101ABCDEF
*/
$route['default_controller'] = 'home';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

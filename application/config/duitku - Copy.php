<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| Kredensial Duitku (ambil dari dashboard https://passport.duitku.com)
| Set 'duitku_sandbox' ke FALSE saat produksi.
| Callback URL di dashboard Duitku: {base_url}payment/callback
*/
$config['duitku_merchant_code'] = getenv('DUITKU_MERCHANT_CODE') ?: 'DS35464';
$config['duitku_api_key']       = getenv('DUITKU_API_KEY') ?: '85a6f8c4aed905f68726067cfda37e15';
$config['duitku_sandbox']       = TRUE;
